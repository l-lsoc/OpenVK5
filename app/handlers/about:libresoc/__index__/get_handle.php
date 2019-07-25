<?php

return (function($args) {
    return [
        "php"     => phpversion(),
        "webserv" => $_SERVER["SERVER_SOFTWARE"],
        "openssl" => phpversion("openssl")
    ];
});
