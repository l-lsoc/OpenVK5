<?php

return (function($args) {
    assert_user($args);
    assert_security($args);

    $db     = open_database();
    $groups = $db->table("groups");
    $group  = $groups->get($args["id"]);
    if(!$group) err();
    if(!canModifyGroupSettings($args["__user__"]->id, $group->id)) err(false, 3, "Forbidden", "Вы не можете изменять эту группу."); 

    $groups->where("id", $group->id)->update([
        "name"   =>   $_POST["name"]   ?? $group->name,
        "about"  =>  $_POST["about" ]  ?? $group->about,
        "status" => $_POST[ "status" ] ?? $group->status,
    ]);

    header("HTTP/1.1 302 Found");
    header("Location: ?/public&id=".$group->id);
    exit;
});
