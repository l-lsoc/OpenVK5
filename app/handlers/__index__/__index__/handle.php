<?php

return (function($args) {
    if(isset($args["__user__"])) {
        header("HTTP/1.1 302 Found");
        header("Location: ?/feed");
        exit;
    }
    
    return [];
});
