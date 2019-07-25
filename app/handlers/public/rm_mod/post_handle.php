<?php

return (function($args) {
    assert_user($args);
    assert_security($args);

    $db     = open_database();
    $groups = $db->table("groups");
    $group  = $groups->get($args["club"]);
    if(!$group) err();
    $hasRights = canModifyGroupSettings($args["__user__"]->id, $args["club"], $args["__user__"]->id !== $group->owner); #Модератор модератору не указ
    if(!$hasRights) err(false, 3, "Forbidden", "Вы не можете удалять модераторов.");

    $admins = array_values(json_decode($group->coadmins, true));
    if(in_array($args["id"], $admins)) {
        $admins = array_diff($admins, [$args["id"]]);
        $groups->where("id", $args["club"])->update(["coadmins" => json_encode($admins)]);
    }

    header("HTTP/1.1 302 Found");
    header("Location: ?/public&act=edit&id=".$args["club"]);
    exit;
});
