<?php

return (function($args) {
    $db    = open_database();
    $users = $db->table("users");

    $input     = explode(" ", $_POST["q"] ?? $_GET["q"]);
    if(iconv_strlen($input[0]) < 3) err(false, 0, "Bad Request", "Введите минимум 4 символа");

    $selection = $users->where("first_name LIKE ?", "%$input[0]%");
    if(isset($input[1])) $selection = $selection->where("last_name LIKE ?", "%$input[1]%");

    $result = $selection->limit(20, (($args["page"] ?? 1) - 1) * 20);

    return ["results" => $result];
});
