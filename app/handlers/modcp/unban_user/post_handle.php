<?php

return (function($args) {
    register_experiment("Admin.ModCP");
    assert_user($args);
    assert_su($args);
    $db   = open_database();
    $user = $db->table("users")->get($_POST["user"]);
    if(!$user) err();

    $db->table("users")->where("id", $user->id)->update(["block_reason" => null]);

    header("HTTP/1.1 302 Redirect");
    header("Location: ?/modcp&act=users");
    exit;
}); 
