<?php

return (function($args) {
    $database = open_database();
    $docs     = $database->table("docs");
    $users    = $database->table("users");

    $user     = $users->get($args["id"] ?? $args["__user__"]->id);
    if(!$user) err();

    $docs     = $docs->where("owner", $user->id)->page($args["page"] ?? 1, 10)->order("id DESC");

    return [
        "docs"    => $docs,
        "owner"   => $user,
    ];
});
