<?php

return (function($args) {
    assert_user($args);
    assert_security($args);

    $db      = open_database();
    $groups  = $db->table("groups");
    $flTable = $db->table("followers");
    $group   = $groups->get($args["club"]);
    if(!$group) err();
    if(!canModifyGroupSettings($args["__user__"]->id, $args["club"])) err(false, 3, "Forbidden", "Вы не можете изменять эту группу");

    $admins = array_values(json_decode($group->coadmins, true));
    if(in_array($args["id"], $admins)) err(false, 3, "Forbidden", "Нельзя удалить модератора");

    $flTable->where([
        "target"   => -1*$args["club"],
        "follower" => $args["id"],
    ])->delete();

    header("HTTP/1.1 302 Found");
    header("Location: ?/public&act=edit&id=".$args["club"]);
    exit;
});
