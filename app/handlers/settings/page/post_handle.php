<?php

return (function($args) {
    assert_user($args);
    assert_security($args);
    $db    = open_database();
    $users = $db->table("users");

    $db->table("users")->where("id", $args["__user__"]->id)->update([
        "first_name" => $_POST["fname"]  ?? $args["__user__"]->first_name,
        "last_name"  => $_POST["lname"]  ?? $args["__user__"]->last_name,
        "pseudo"     => $_POST["pseudo"] ?? "",
        "status"     => $_POST["status"] ?? $args["__user__"]->status,
        "info"       => $_POST["about"]  ?? $args["__user__"]->info,
    ]);

    header("HTTP/1.1 302 Redirect");
    header("Location: ?/user&id=".$args["__user__"]->id);
    exit;
}); 
