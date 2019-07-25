<?php

return (function($args) {
    assert_user($args);
    $database  = open_database();
    $user      = $database->table("users")->get($args["id"] ?? $args["__user__"]->id);
    if(!$user) err();

    list($friends, $followers, $subscriptions) = getEntityRelations($user, $args["page"] ?? 1);

    return [
         "friends"   => $friends,
         "followers" => $followers,
         "subs"      => $subscriptions,
    ];
});
