<?php

return (function($args) {
    assert_user($args);
    assert_security($args, true, false);
    assert_post("title", "about");

    $database = open_database();
    $album    = $database->table("albums")->insert([
        "owner"  => $args["__user__"]->id,
        "title"  => $_POST["title"],
        "desc"   => $_POST["about"],
        "photos" => "[]",
    ]);

    header("HTTP/1.1 302 Found");
    header("Location: ?/photos&act=list&id=".$album->id);
    exit;
});
