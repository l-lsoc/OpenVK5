<?php

return (function($args) {
    assert_user($args);

    return ["user" => $args["__user__"]];
});
