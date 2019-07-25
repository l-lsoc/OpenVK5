<?php

function mkhash($password)
{
    $salt = bin2hex(openssl_random_pseudo_bytes(12));
    $hash = hash("whirlpool", $password.$salt);
    
    return "$hash\$$salt";
}

function mkuser($j)
{
    file_put_contents("passes.txt", file_get_contents("passes.txt")."\r\n$j[pass]");
    return [
        "first_name" => $j["first-name"],
        "last_name"  => $j["last-name"],
        "pseudo"     => "",
        "info"       => "",
        "about"      => json_encode([
            "bday" => implode("", [$j["bd-year"], $j["bd-month"], $j["bd-day"]])
        ]),
        "email"      => $j["email"],
        "phone"      => $j["phone"],
        "status"     => "",
        "now_listening" => 0,
        "is_talking"    => "no",
        "role"          => 0,
        "last_action"   => date(DATE_W3C), #SQLite date
        "last_device"   => "pc",
        "since"         => date(DATE_W3C),
        "blacklist"     => "[]",
        "blocked"       => "no",
        "block_reason"  => null,
        "dead"          => "no",
        "verified"      => "no",
        "css"           => null,
        "achievments"  => "[]",
        "reputation"    => 1,
        "people_reach"  => 0,
        "login"         => $j["login"],
        "password_hash" => mkhash($j["pass"]),
        "coins"         => 0,
        "sex"           => $j["sex"]? "male" : "female",
    ];
}

/**
* Регистрация
*/
return (function($args) {
    assert_security($args);
    assert_post("first-name", "last-name", "sex", "bd-year", "bd-month", "bd-day", "phone", "email", "login", "pass", "invite", "consent");
    
    if($_POST["consent"] !== "on") {
        header("HTTP/1.1 302 Found");
        header("Location: ".SOCN_ROOT."/?/register&f=2");
    }

    $database = DB::getInstance();
    $database = $database->getContext();
    
    try {
        $user = $database->table("users")->insert(mkuser($_POST));
    } catch(Exception $e) {
        header("HTTP/1.1 302 Found");
        if(strpos($e->getMessage(), "UNIQUE") !== false) {
            $error = (int) (strpos($e->getMessage(), "users.email") !== false);
            header("Location: ".SOCN_CONFIG["URL"]."/?/register&f=$error");
            exit;
        }
        
        header("Location: ".SOCN_CONFIG["URL"]."/?/register&f=23000");
        exit;
    }

    header("HTTP/1.1 302 Found");
    header("Location: ".SOCN_CONFIG["URL"]."/?/login&f=100");

    exit;
});
