<?php

return (function($args) {
    $database = open_database();
    $video    = $database->table("videos")->get($args["id"]);
    if(!$video) err();
    $owner    = $video->ref("users", "owner");

    return ["video" => $video, "owner" => $owner];
});
