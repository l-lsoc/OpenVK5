<?php

return (function($args) {
    assert_user($args);
    assert_security($args, true, false);
    assert_post("post");

    $database = open_database();
    $posts    = $database->table("posts");

    $post     = $posts->get($args["id"]);
    if(!$post) err();

    if(!postOwner($args["__user__"]->id, $post)) err(false, 3, "Forbidden", "Вы не можете редактировать этот пост.");

    if(empty($_POST["post"])) err(false, 0, "Bad Request", "Пост не может быть пустым");

    $posts->where("id", $post->id)->update([
        "content" => preg_replace("%(\s)[\s]++%", "$1", $_POST["post"]),
        "edited"  => date(DATE_W3C),
    ]);

    header("HTTP/1.1 302 Found");
    header("Location: ?/".(($post->owner < 0)? "public" : "user")."&id=".abs($post->owner));
    exit;
});
