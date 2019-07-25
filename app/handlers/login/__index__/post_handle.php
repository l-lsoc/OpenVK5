<?php

function authenticateUser(string $login, string $password): void
{
    $database = DB::getInstance();
    $database = $database->getContext();
    $users    = $database->table("users");
    
    $user     = $users->where("login", $login)->fetch();
    if(!$user) throw new Exception;
    
    list($hash, $salt) = explode("$", $user->password_hash);
    if(!hash_equals($hash, hash("whirlpool", "$password$salt"))) throw new Exception;
    
    State::set("uuid", $user->id);
    State::set("tok", mktoken($user->id, $_SERVER["REMOTE_ADDR"]));
}

/**
* Вход
*/
return (function($args) {
    assert_security($args, true, true);
    assert_post("login", "pass");
    
    try {
        authenticateUser($_POST["login"], $_POST["pass"]);
    } catch(Exception $e) {
        return ["error" => (object) [
            "type" => "error",
            "desc" => "Неверный логин или пароль",
        ]];
    }
    
    header("HTTP/1.1 302 Found");
    header("Location: ?/");
    exit;
});
