<?php

return (function($args) {
    register_experiment("Admin.ModCP");
    assert_user($args);
    assert_su($args);
    $db   = open_database();
    $user = $db->table("users")->get($_POST["id"]);
    if(!$user) err();

    return ["user" => $user];
});
