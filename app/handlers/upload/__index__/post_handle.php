<?php
use Nette\Utils\FileSystem;
use Nette\Utils\Image;

function getVideoDuration(string $stdout): int
{
    preg_match('/Duration: ([0-9]{2}):([0-9]{2}):([^ ,])+/', $output, $matches);
    $time = str_replace("Duration: ", "", $matches[0]);
    $time_breakdown = explode(":", $time);
    $total_seconds = floor(($time_breakdown[0]*60*60) + ($time_breakdown[1]*60) + $time_breakdown[2]);
    return $total_seconds;
}

function processDocument(int $owner, bool $echo = true)
{
    $database  = open_database();
    $documents = $database->table("docs");

    $file = $_FILES["blob"]["tmp_name"];
    $hash = hash_file("whirlpool", $file);

    $dirname = SOCN_ROOT."private/userblob_storage/".substr($hash, 0, 2)."/";
    FileSystem::createDir($dirname);
    move_uploaded_file($_FILES["blob"]["tmp_name"], $dirname.$hash);

    $time     = bin2hex(time());
    $document = $documents->insert([
        "id"                => uniqid("userblob_$time_", true),
        "owner"             => $owner,
        "title"             => $_POST["title"],
        "hash"              => $hash,
        "type"              => 0,
        "original_filename" => $_FILES["blob"]["name"],
        "estimated_size"    => $_FILES["blob"]["size"],
    ]);

    $echo? print_r(json_encode(["result" => true, "id" => $document->id])) : null;
    return $document->id;
}

function processImage(int $owner, bool $echo = true)
{
    $database = open_database();
    $photos   = $database->table("photos");

    $count   = 1;
    $results = [];

    $count = sizeof($_FILES["blob"]["name"]);
    for($i = 0; $i < $count; $i++) {
        $file = $_FILES["blob"]["tmp_name"][$i];
        $hash = hash_file("whirlpool", $file);

        $potentialPhoto = $photos->where(["owner" => $owner, "file" => $hash])->fetch();
        if(!is_null($potentialPhoto)) {
            $results[] = $potentialPhoto;
            continue;
        }

        $dirname = SOCN_ROOT."public/cdn/images/".substr($hash, 0, 2)."/";
        FileSystem::createDir($dirname);
        FileSystem::createDir($dirname.$hash);

        try {
            $image = Image::fromFile($file);
        } catch(Exception $e) {
            exit(json_encode(["result" => false]));
        }

        $image->resize("1280", null, Image::SHRINK_ONLY);
        $image->sharpen();
        $image->save($dirname.$hash."/large.png", Image::PNG);
        $image->resize("640", "1280", Image::SHRINK_ONLY);
        $image->sharpen();
        $image->save($dirname.$hash."/medium.png", Image::PNG);
        $image->resize("128", "256", Image::SHRINK_ONLY);
        $image->sharpen();
        $image->save($dirname.$hash."/thumbnail.gif", Image::GIF);

        $results[] = $photos->insert([
            "owner" => $owner,
            "file"  => $hash,
            "desc"  => $_POST["desc"] ?? "",
            "nsfw"  => ($_POST["nsfw"] ?? "off") === "on",
        ]);
    }

    $echo? print_r(json_encode(["result" => true, "id" => $results[0]->id, "hash" => $results[0]->file])) : null;
    return ($count > 1)? $results : $results[0]->id;
}

function processAlbumPhoto($photos, int $album, int $user, bool $verify = true): void
{
    $database = open_database();
    $albums   = $database->table("albums");
    $rels     = $database->table("photo_relations");

    $album    = $albums->get($album);
    if(!$album) err();
    if($verify && !isOwner($user, $album)) err(false, 3, "Forbidden", "Вы не можете изменять этот альбом.");

    if(gettype($photos) !== "array") $photos = [$photos];

    foreach($photos as $photo) $rels->insert(["album" => $album->id, "photo" => $photo]);

    header("HTTP/1.1 302 Found");
    header("Location: ?/photos&act=list&id=".$album->id);
    exit;
}

function processAvatar(int $photo, int $user, int $group = null, bool $verifyGroup = true): void
{
    if(!is_null($group) && $verifyGroup) {
        if(!canModifyGroupSettings($user, $group)) exit(header("HTTP/1.1 403 Forbidden"));
    }
    $id = is_null($group)? $user : -1*$group;

    $database = open_database();
    $database->table("avatars")->insert(["id" => $id, "photo" => $photo, "since" => date(DATE_W3C)]);

    header("HTTP/1.1 302 Found");
    is_null($group)? header("Location: ?/user&id=0") : header("Location: ?/public&id=".$group);
    exit;
}

function processVideo(int $owner, bool $echo = true): int
{
    set_time_limit(0); #Video processing :DDD

    $database = open_database();
    $videos   = $database->table("videos");

    $file = $_FILES["blob"]["tmp_name"];
    $hash = hash_file("whirlpool", $file);

    $dirname = SOCN_ROOT."public/cdn/videos/".substr($hash, 0, 2)."/";
    FileSystem::createDir($dirname);
    $filename = $dirname.$hash.".webm";
    $imgname  = $dirname.$hash.".thumb.gif";

    $hvid = SOCN_ROOT."/app/handlers/upload/__index__/video/handle_video";
    $vid  = `nohup $hvid '$file' '$filename' '$imgname' > /tmp/nohup.log 2> /tmp/nohup.error & disown`;
    sleep(1);
    //if(!$vid) exit(json_encode(["result" => false])); TODO понять почему сосёт, а то ***.

    $video = $videos->insert([
        "owner" => $owner,
        "file"  => $hash,
        "title" => $_POST["title"] ?? "Без названия",
        "desc"  => $_POST["about"] ?? "",
        "nsfw"  => ($_POST["nsfw"] ?? "off") === "on",
    ]);

    $echo? print_r(json_encode(["result" => true, "id" => $video->id, "hash" => $hash])) : null;
    return $video->id;
}

return (function($args) {
    assert_file("blob");
    assert_user($args);
    assert_security($args, false, false);

    switch($args["type"]) {
        case "image":
            processImage($args["__user__"]->id);
        break;
        case "photo":
            processAlbumPhoto(processImage($args["__user__"]->id, false), $args["album"], $args["__user__"]->id);
        break;
        case "avatar":
            processAvatar(processImage($args["__user__"]->id, false), $args["__user__"]->id, $args["group"]);
        break;
        case "document":
            $doc = processDocument($args["__user__"]->id);
            if($args["redirect"]) {
                header("HTTP/1.1 302 Found");
                header("Location: ?/docs&id=".$args["__user__"]->id);
                exit;
            }
        break;
        case "video":
            $video = processVideo($args["__user__"]->id);
            if($args["redirect"]) {
                header("HTTP/1.1 302 Found");
                header("Location: ?/videos&act=view&id=".$video);
                exit;
            }
        break;
        default:
            header("HTTP/1.1 400 Bad Request");
            exit;
    }

    header("HTTP/1.1 202 Accepted");
    exit;
});
