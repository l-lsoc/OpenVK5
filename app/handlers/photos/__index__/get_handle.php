<?php

return (function($args) {
    $database = open_database();
    $albums   = $database->table("albums");
    $users    = $database->table("users");

    $user  = $users->get($args["id"] ?? $args["__user__"]->id);
    if(!$user) err();

    $albums    = $albums->where("owner", $user->id)->page($args["page"] ?? 1, 10)->order("id ASC");

    return [
        "albums"    => $albums,
        "owner"     => $user,
    ];
});
