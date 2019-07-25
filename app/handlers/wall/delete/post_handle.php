<?php

return (function($args) {
    assert_user($args);
    assert_security($args, true, false);

    $database = open_database();
    $posts    = $database->table("posts");

    $post     = $posts->get($args["id"]);
    if(!$post) err();

    if(!canDeletePost($args["__user__"]->id, $post)) err(false, 3, "Forbidden", "Вы не можете удалять этот пост.");

    $target = $post->target;
    $posts->where("id", $post->id)->delete();

    header("HTTP/1.1 302 Found");
    header("Location: ?/".(($target < 0)? "public" : "user")."&id=".abs($target));
    exit;
});
