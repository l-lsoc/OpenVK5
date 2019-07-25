<?php

return (function($args) {
    assert_user($args);
    if(!in_array($args["type"], ["post", "comment", "message", "photo", "video"])) err(false, 0, "Bad Request", "$args[type] doesn't seem to be reportable");

    return [
        "id"   => $args["id"],
        "type" => $args["type"]
    ];
});
