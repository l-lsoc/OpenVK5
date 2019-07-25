<?php

return (function($args) {
    assert_user($args);

    $db      = open_database();
    $flTable = $db->table("followers");
    $posts   = $db->table("posts");
    $user    = $db->table("users")->get($args["__user__"]->id);

    $targets    = [];
    $targetsRaw = $flTable->select("target AS id")->where("follower", $args["__user__"]->id);
    foreach($targetsRaw as $raw) $targets[] = $raw->id;

    $posts = $posts->where("owner", $targets)->order("date DESC")->page($_GET["page"] ?? 1, 10);

    return [
        "user" => $user,
        "wall" => $posts,
    ];
});
