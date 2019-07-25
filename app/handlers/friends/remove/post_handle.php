<?php

return (function($args) {
    assert_security($args, true, false);

    $database  = open_database();
    $relations = $database->table("followers");
    $params    = [
        "follower" => $args["__user__"]->id,
        "target" => $args["id"],
    ];

    $relations->where($params)->delete();

    header("HTTP/1.1 302 Found");
    header("Location: ".SOCN_CONFIG["URL"]."/?/".($args["id"] < 0 ? "public" : "user")."&id=".abs($args["id"]));
    exit;
});
