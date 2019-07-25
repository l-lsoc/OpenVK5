<?php

return (function($args) {
    assert_user($args);
    assert_security($args, true, false);

    $database = open_database();
    $album    = $database->table("albums")->get($args["id"]);
    if(!$album) err();
    if(!isOwner($args["__user__"]->id, $album)) err(false, 3, "Forbidden", "Вы не можете изменять этот альбом.");

    $database->table("albums")->where("id", $album->id)->delete();

    header("HTTP/1.1 302 Found");
    header("Location: ?/photos&id=".$album->owner);
    exit;
});
