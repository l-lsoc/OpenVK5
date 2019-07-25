<?php

return (function($args) {
    $database = open_database();
    $photo    = $database->table("photos")->get($args["id"]);
    if(!$photo) err();

    return ["photo" => $photo];
});
