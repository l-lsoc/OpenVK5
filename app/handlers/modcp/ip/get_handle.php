<?php

return (function($args) {
    register_experiment("Admin.ModCP");
    assert_user($args);
    assert_su($args);

    return [];
}); 
