<?php

function hasWithID($needle, $haystack): bool
{
    return in_array($needle->id, iterator_to_array(fetchIDs($haystack)));
}

return (function($args) {
    $database  = open_database();
    $users     = $database->table("users");
    $relations = $database->table("followers");
    $posts     = $database->table("posts");

    if($args["id"] == 0 || !isset($args["id"])) {
        if(!$args["__user__"]) err();

        $user = $args["__user__"];
    } else {
        $user = $users->get($args["id"]);
        if(!$user) err();
    }

    list($friends, $subst, $subso, $clubs) = getEntityRelations($user);

    $followers  = [];
    $subscribes = [];
    foreach($subst as $j) $followers[]  = $users->get($j->id);
    foreach($subso as $j) $subscribes[] = $users->get($j->id);

    $wall    = $posts->where("target", $user->id)->page($args["page"] ?? 1, 10)->order("date DESC");

    $is_friend   = !isset($args["__user__"])? false : hasWithID($args["__user__"], $friends);
    $is_follower = !isset($args["__user__"])? false : hasWithID($args["__user__"], $subso);
    $req_sent    = !isset($args["__user__"])? false : hasWithID($args["__user__"], $subst);

    $albums = $database->table("albums")->select("COUNT(*) AS cnt")->where("owner", $user->id)->fetch()->cnt;
    $videos = $database->table("videos")->select("COUNT(*) AS cnt")->where("owner", $user->id)->fetch()->cnt;

    return [
        "user"       => $user,
        "friends"    => $friends,
        "subscribed" => $subscribes,
        "followers"  => $followers,
        "clubs"      => $clubs,
        "wall"       => $wall,
        "page"       => $args["page"] ?? 1,
        
        "is_friend"    => $is_friend,
        "is_follower"  => $is_follower,
        "request_sent" => $req_sent,
        
        "albums" => $albums,
        "videos" => $videos,
    ];
});
