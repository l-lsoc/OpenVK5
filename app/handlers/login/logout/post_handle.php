<?php
/**
* Выход
*/
return (function($args) {
    assert_security($args, true, false);
    
    State::forget("uuid");
    State::forget("tok");
    
    header("HTTP/1.1 302 Found");
    header("Location: ?/");
    exit;
});
 
