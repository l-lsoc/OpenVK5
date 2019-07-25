<?php

return (function($args) {
    assert_user($args);
    assert_security($args, true, true);
    assert_post("name", "topic", "type");

    $types = ["group" => 0, "public" => 1, "event" => -1];

    $database = open_database();
    $groups   = $database->table("groups");
    $subs     = $database->table("followers");

    $group = $groups->insert([
        "name"      => $_POST["name"],
        "info"      => "{'topic': $_POST[topic]}",
        "about"     => "",
        "status"    => "",
        "verified"  => "no",
        "owner"     => $args["__user__"]->id,
        "coadmins"  => "[]",
        "followers" => "",
        "type"      => $types[$_POST["type"]],
    ]);
    $subs->insert([
        "follower" => $args["__user__"]->id,
        "target"   => -1 *$group->id,
    ]);

    header("HTTP/1.1 302 Found");
    header("Location: ?/public&id=".$group->id);
    exit;
});
