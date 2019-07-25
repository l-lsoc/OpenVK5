<?php

return (function($args) {
    assert_user($args);
    assert_security($args, true, false);

    $database = open_database();
    $videos   = $database->table("videos");

    $video = $videos->get($args["id"]);
    if(!$video) err();

    if(!isOwner($args["__user__"]->id, $video)) err(false, 3, "Forbidden", "Вы не можете изменять это видео.");

    $videos->where("id", $video->id)->update([
        "title" => $_POST["title"] ?? $video->title,
        "desc"  => $_POST["about"] ?? $video->desc,
    ]);

    header("HTTP/1.1 302 Found");
    header("Location: ?/videos&act=view&id=".$video->id);
    exit;
});
