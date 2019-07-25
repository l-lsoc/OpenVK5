<?php

return (function($args) {
    $database = open_database();
    $videos   = $database->table("videos");
    $users    = $database->table("users");

    $user     = $users->get($args["id"] ?? $args["__user__"]->id);
    if(!$user) err();

    $videos   = $videos->where("owner", $user->id)->page($args["page"] ?? 1, 10)->order("id DESC");

    return [
        "videos"    => $videos,
        "owner"     => $user,
    ];
});
