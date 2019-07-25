<?php

function getFollowerGStats(int $id): array
{
    $db      = open_database(false);
    $all     = $db->query("SELECT COUNT(*) AS n FROM followers WHERE target=?", -1*$id)->fetch()->n;
    $females = $db->query("SELECT COUNT(*) AS n FROM (SELECT follower FROM followers WHERE target = ?) f0 INNER JOIN (SELECT id FROM users WHERE sex='female') uf ON f0.follower = uf.id", -1*$id)->fetch()->n;

    return [$all - $females, $females];
}

return (function($args) {
    assert_user($args);

    $db      = open_database();
    $flTable = $db->table("followers");
    $group   = $db->table("groups")->get($args["id"]);
    if(!$group) err();
    if(!canModifyGroupSettings($args["__user__"]->id, $group->id)) err(false, 3, "Forbidden", "Вы не можете изменять эту группу.");

    $followerGStats = getFollowerGStats($group->id);
    $followers = (object) [
         "femaleCount" => $followerGStats[1],
         "maleCount"   => $followerGStats[0],
         "followers"   => $flTable->select("follower AS id")->where("target", -1*$group->id)->page($args["page"] ?? 1, 10),
    ];

    return ["club" => $group, "followers" => $followers];
});
