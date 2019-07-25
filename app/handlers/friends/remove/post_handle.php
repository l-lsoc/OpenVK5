<?php

return (function($args) {
    assert_security($args, true, false);

    $database  = open_database();
    $relations = $database->table("followers");
    $groups    = $database->table("groups");
    $params    = [
        "follower" => $args["__user__"]->id,
        "target" => $args["id"],
    ];

    $relations->where($params)->delete();

    if($args["id"] < 0) {
        $group = $groups->get(abs($args["id"]));
        $admins = array_values(json_decode($group->coadmins, true));
        if(in_array($args["__user__"]->id, $admins)) {
            $admins = array_diff($admins, [$args["__user__"]->id]);
            $groups->where("id", $group->id)->update(["coadmins" => json_encode($admins)]);
        }
    }

    header("HTTP/1.1 302 Found");
    header("Location: ".SOCN_CONFIG["URL"]."/?/".($args["id"] < 0 ? "public" : "user")."&id=".abs($args["id"]));
    exit;
});
