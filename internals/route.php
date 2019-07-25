<?php
require(SOCN_ROOT."internals/router.php");
State::initSession(true);
$GLOBALS["csrf_token"] = State::get("csrf_token", bin2hex(openssl_random_pseudo_bytes(64)), true);

$request_uri = $_SERVER["QUERY_STRING"] ?? "/";
if($request_uri[0] === "/") {
    $request_uri = substr($request_uri, 1);
}

$controller = preg_replace("%([\/?\&#](.*)?)%", "", $request_uri);
$act        = $_GET["act"];
$params     = array_diff_key($_GET, ["act" => $act, "/".$controller => ""]);

route($controller, $act, $params);
