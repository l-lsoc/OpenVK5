<?php

return (function($args) {
    assert_user($args);
    assert_security($args, true, false);
    assert_post("comment");

    $database = open_database();
    $post     = $database->table("posts")->get($args["id"]);
    if(!$post) err();

    mkcomment($args["__user__"]->id, $args["id"], "Post", $_POST["comment"]);

    header("HTTP/1.1 302 Found");
    header("Location: /?/".(($post->target > 0)? "user" : "public")."&id=".abs($post->target)."#_post".$post->id);
    exit;
});
