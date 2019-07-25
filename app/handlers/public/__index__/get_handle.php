<?php

return (function($args) {
    $database  = open_database();
    $groups    = $database->table("groups");
    $flTable   = $database->table("followers");
    $posts     = $database->table("posts");

    if(!isset($args["id"])) err(false, 0, "Bad Request", "ID группы не указан");
    $group = $groups->get($args["id"]);
    if(!$group) err();

    $subscribed = false;
    if(isset($args["__user__"])) {
        $entry = $flTable->where([
            "follower" => $args["__user__"]->id,
            "target" => -1*$args["id"]
        ])->fetch();
        if(!is_null($entry)) $subscribed = true;
        $flTable = $database->table("followers");
    }

    $followers = $flTable->select("follower AS id")->where("target", -1*$args["id"])->limit(6);

    $wall = $posts->where("target", -1*$args["id"])->page($args["page"] ?? 1, 10)->order("date DESC");

    return [
         "club"       => $group,
         "subscribed" => $subscribed,
         "followers"  => $followers,
         "wall"       => $wall,
    ];
});


