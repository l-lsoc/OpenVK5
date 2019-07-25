<?php

return (function($args) {
    assert_user($args);
    $user = open_database()->table("users")->get($args["id"] ?? $args["__user__"]->id);

    if(!$user) err();

    return ["user" => $user];
});
