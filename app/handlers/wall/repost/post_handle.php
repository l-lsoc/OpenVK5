<?php

return (function($args) {
    assert_user($args);
    assert_security($args, true, false);

    $database = open_database();
    $post = $database->table("posts")->insert([
        "owner"    => $args["__user__"]->id,
        "liked_by" => "[]",
        "target"   => $args["__user__"]->id,
        "date"     => date(DATE_W3C),
        "edited"   => null,
        "content"  => preg_replace("%(\s)[\s]++%", "$1", ($_POST["message"] ?? '')),
        "attachments" => "[]",
    ]);
    $att = $database->table("posts")->get($args["id"]);
    if(!$att) err();

    $database->table("post_attachments")->insert([
        "target"          => $post->id,
        "attachable_type" => "Post",
        "attachable_id"   => $args["id"],
    ]);

    header("HTTP/1.1 302 Found");
    header("Location: ?/user&id=0#_post".$post->id);
    exit;
});
