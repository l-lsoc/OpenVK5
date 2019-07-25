<?php

return (function($args) {
    assert_user($args);
    assert_security($args, true, false);

    $database = open_database();
    $photos   = $database->table("photos");

    $photo = $photos->get($args["id"]);
    if(!$photo) err();

    if(!isOwner($args["__user__"]->id, $photo)) err(false, 3, "Forbidden", "Вы не можете изменять это фото.");

    $photos->where("id", $photo->id)->update([
        "desc"  => $_POST["about"] ?? $photo->desc,
    ]);

    header("HTTP/1.1 302 Found");
    header("Location: ?/photos&act=view&id=".$photo->id);
    exit;
});
