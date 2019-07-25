<?php

#TODO add privacy settings support
return (function($args) {
    assert_user($args);
    assert_security($args, false, false);
    assert_post("post");

    $id       = $args["id"] ?? $args["__user__"]->id;
    $database = open_database();

    if($id > 0) {
        $user  = $database->table("users")->get($id);
        if(!$user) exit(header("HTTP/1.1 400 Bad Request"));
    } else {
        $group = $database->table("groups")->get(abs($id));
        if(!$group) exit(header("HTTP/1.1 400 Bad Request"));

        if($_POST["as_club"] === "on" && canModifyGroupSettings($args["__user__"]->id, abs($id))) $as = $id;
    }

    if(empty($_POST["post"])) err(false, 0, "Bad Request", "Пост не может быть пустым");

    $post = $database->table("posts")->insert([
        "owner"    => $as ?? $args["__user__"]->id,
        "liked_by" => "[]",
        "target"   => $id,
        "date"     => date(DATE_W3C),
        "edited"   => null,
        "content"  => preg_replace("%(\s)[\s]++%", "$1", $_POST["post"]),
        "attachments" => "[]",
    ]);

    if(isset($_POST["attachments"])) {
        $attachments = array_slice(explode(",", $_POST["attachments"]), 1);
        $attTable    = $database->table("post_attachments");
        foreach($attachments as $attachment) {
            list($type, $aid) = explode("::", $attachment);
            if(!verifyAttachmentExists($type, $aid)) err(false, 0, "Bad request", "Состояние вложения изменилось.");

            $attTable->insert([
                "target"          => $post->id,
                "attachable_type" => $type,
                "attachable_id"   => $aid,
            ]);
        }
    }

    header("HTTP/1.1 302 Found");
    header("Location: /?/".(($id > 0)? "user" : "public")."&id=".abs($id));
    exit;
});
