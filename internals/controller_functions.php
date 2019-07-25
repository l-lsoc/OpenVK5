<?php
include_once SOCN_ROOT."internals/openvk/zmdate.php";

function fetchIDs($haystack): Traversable
{
    foreach($haystack as $j) yield $j->id;
}

function open_database($context = true)
{
    $database = DB::getInstance();
    return $context? $database->getContext() : $database->getConnection();
}

function register_experiment(string $id): void
{
   if(!in_array($id, SOCN_CONFIG["EXPERIMENTS"])) die("Модуль запрещён политикой");
}

function assert_su(array $args): void
{
    if($args["__user__"]->id !== SOCN_CONFIG["ADMIN_ID"]) err(false, 3, "Forbidden");
}

function assert_security(array $args, bool $csrf = true, bool $captcha = false): void {
    if($csrf && !$args["__csrf__"]) {
        header("HTTP/1.1 400 Bad Request");
        header("Location: &glf=1");
    };
    if($captcha && !$args["__captcha__"]) {
        header("HTTP/1.1 400 Bad Request");
        header("Location: &glf=2");
        exit;
    }
}

function assert_post(...$params): void {
    foreach($params as $param) {
        if(!isset($_POST[$param])) {
            header("HTTP/1.1 400 Bad Request");
            header("Location: &glf=3");
            exit;
        }
    }
}

function assert_file(...$files): void {
    foreach($files as $file) {
        if(!isset($_FILES[$file]) || empty($_FILES[$file]["tmp_name"])) {
            header("HTTP/1.1 400 Bad Request");
            header("Location: &glf=3");
            exit;
        }
    }
}

function assert_user($args): void
{
    if(!isset($args["__user__"])) {
        header("HTTP/1.1 403 Forbidden");
        exit;
    }
}

function mktoken(int $id, string $ip): string
{
    $database = DB::getInstance();
    $database = $database->getContext();
    $tokens   = $database->table("tokens");
    
    $token = $tokens->where(["user" => $id, "ip" => $ip])->fetch();
    if(!$token) {
        $token = bin2hex(openssl_random_pseudo_bytes(64));
        $tokens->insert([
            "user"  => $id,
            "ip"    => $ip,
            "token" => $token,
        ]);
        
        return $token;
    }

    return $token->token;    
}

function mkcomment(int $owner, int $id, string $type, string $content)
{
    $database = open_database();
    $comments = $database->table("comments");

    $comment  = $comments->insert([
        "owner"            => $owner,
        "commentable_type" => $type,
        "commentable_id"   => $id,
        "liked_by"         => "[]",
        "date"             => date(DATE_W3C),
        "edited"           => null,
        "content"          => preg_replace("%(\s)[\s]++%", "$1", $content),
    ]);

    return $comment;
}

function getAvUrl(int $id, int $size = 1): string {
    $sizes = ["thumbnail.gif", "medium.png", "large.png"];

    $db = open_database();
    $av = $db->table("avatars")->where("id", $id)->order("since DESC")->fetch();
    if(!$av) return "no_photo.jpeg";

    $av = $av->ref("photos", "photo");
    if(!$av) return "no_photo.jpeg";

    $hash = $av->file;
    return "images/".substr($hash, 0, 2)."/$hash/$sizes[$size]";
}

function getEntityRelations($user, int $page = 1): array {

    $friendsQuery = <<<'EOT'
        SELECT * FROM
            (SELECT follower AS id FROM
                (SELECT follower FROM followers WHERE target=?) u0
            INNER JOIN
                (SELECT target FROM followers WHERE follower=?) u1
            ON u0.follower = u1.target) u2
        INNER JOIN users ON users.id = u2.id
EOT;

    $database = open_database();

    $friends = iterator_to_array((function($friends) use ($database) {
        foreach($friends as $friend) yield $database->table("users")->get($friend->id);
    })(open_database(false)->query($friendsQuery." LIMIT 6 OFFSET ".(($page - 1) * 6), $user->id, $user->id)));

    $subst   = $database
                   ->table("followers")
                   ->select("follower AS id")
                   ->where("target", $user->id)
                   ->where("follower NOT", iterator_to_array(fetchIDs($friends)))
                   ->page($page, 6);
    $subso   = $database
                   ->table("followers")
                   ->select("target AS id")
                   ->where("follower", $user->id)
                   ->where("target > 0")
                   ->where("target NOT", iterator_to_array(fetchIDs($friends)))
                   ->page($page, 6);
    $clubs   = $database->table("followers")->select("target AS id")->where("follower", $user->id)->where("target < 0")->page($page, 6);

    return [$friends, $subst, $subso, $clubs];
}

function canModifyGroupSettings(int $user, int $group, bool $strict = false): bool
{
    $database = open_database();
    $group = $database->table("groups")->get($group);
    if(!$group) return false;

    return ($user === $group->owner) || ( $strict ? false : (in_array($user, array_values(json_decode($group->coadmins, true)))) );
}

function isOwner(int $user, object $entity) {
    if($entity->owner > 0)
        return $entity->owner === $user;
    
    return canModifyGroupSettings($user, abs($entity->owner));
}

function postOwner(int $user, $post): bool
{
    return isOwner($user, $post);
}

function canDeletePost(int $user, $post): bool
{
    if(postOwner($user, $post)) return true;
    
    if($post->target < 0)
        return (canModifyGroupSettings($user, abs($post->target)));
 
    return $post->target === $user;
}

function verifyAttachmentExists(string $type, int $id): bool
{
    $database = open_database();
    switch($type)
    {
        case "Image":
            $photo = $database->table("photos")->get($id);
            if(!$photo) return false;
            break;
        case "Video":
            $video = $database->table("videos")->get($id);
            if(!$video) return false;
            break;
        default:
            return false;
    }

    return true;
}

function err(bool $server = false, int $code = 4, string $title = "Not Found", string $description = ""): void
{
    $code = ($server? 500 : 400) + $code;
    
    header("HTTP/1.1 $code $title");
    exit($description);
}
