<?php

return (function($args) {
    assert_user($args);
    assert_security($args);

    $db     = open_database();
    $groups = $db->table("groups");
    $group  = $groups->get($args["club"]);
    if(!$group) err();
    if(!canModifyGroupSettings($args["__user__"]->id, $args["club"])) err(false, 3, "Forbidden", "Вы не можете изменять эту группу");

    $admins = array_values(json_decode($group->coadmins, true));
    var_export($admins);
    if(!in_array($args["id"], $admins)) {
        array_unshift($admins, $args["id"]);
        $groups->where("id", $args["club"])->update(["coadmins" => json_encode($admins)]);
    }

    header("HTTP/1.1 302 Found");
    header("Location: ?/public&act=edit&id=".$args["club"]);
    exit;
});
