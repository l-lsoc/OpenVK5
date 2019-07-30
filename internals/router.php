<?php
use Nette\Database\Table\ActiveRow;

set_exception_handler(function($e): bool {
    require(SOCN_ROOT."internals/internal_templates/500.phtml");
    throw $e;
    exit;
    return true;
});

function realIp()
{
    return $_SERVER["HTTP_X_REAL_IP"] ?? $_SERVER["HTTP_X_TRUE_CLIENT_IP"] ?? $_SERVER["REMOTE_ADDR"];
}

function _currentUser(): ?ActiveRow
{
    $database = DB::getInstance();
    $database = $database->getContext();
    $tokens   = $database->table("tokens");
    $users    = $database->table("users");

    $user = State::get("uuid");
    $tok  = State::get("tok");
    if(is_null($user) || is_null($tok)) return null;
    
    $ip    = realIp();
    $token = $tokens->where(["user" => $user, "ip" => $ip])->fetch();
    if(!$token) return null;
    if(!hash_equals($token->token, $tok)) return null;
    
    $user = $users->get($user);
    if(!is_null($user->block_reason)) exit("<b>Произошла ошибка</b>: вас забанили по причине <em>".$user->block_reason."</em>. Сожалеем об этом.");
    
    return $user;
}

function _validateCaptcha(string $key, string $token, bool $ip): bool
{
    $token = rawurlencode($token);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://hcaptcha.com/siteverify");
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, [
        "secret"   => $key,
        "response" => $token,
    ]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_AUTOREFERER, true);
    curl_setopt($curlHandle, CURLOPT_HTTPHEADER, [
        "Content-Type: x-www-form/urlencoded",
    ]);
    
    $response = curl_exec($curl);
    curl_close($curl);
    
    return (bool) json_decode($response, true)["success"];
}

function _defaultParams(): array
{
    $csrf    = false;
    $captcha = false;
    
    if($_SERVER["REQUEST_METHOD"] === "POST") {
        if(hash_equals($GLOBALS["csrf_token"], $_POST["__csrf"])) $csrf = true;
        
        if(isset($_POST["h-captcha-response"])) {
            $captcha = _validateCaptcha(SOCN_CONFIG["CAPTCHA_KEY"], $_POST["h-captcha-response"], SOCN_CONFIG["CAPTCHA_IP"]);
        }
        
        $GLOBALS["csrf_token"] = State::set("csrf_token", bin2hex(openssl_random_pseudo_bytes(64)));
    }
    
    return [
        "__csrf__"    => $csrf,
        "__captcha__" => $captcha,
        "__user__"    => _currentUser(),
        "__url__"     => SOCN_CONFIG["URL"] . (defined("MOBILE")? "/mobile" : ""),
    ];
}

function _defaultTemplateParams(): array
{
    return [
        "website_name"    => SOCN_CONFIG["SITENAME"],
        "url"             => SOCN_CONFIG["URL"] . (defined("MOBILE")? "/mobile" : ""),
        "captcha"         => "<div class='h-captcha' data-sitekey='".SOCN_CONFIG["CAPTCHA_SKD"]."'></div>",
        "csrf_protection" => "<input type='hidden' name='__csrf' value='$GLOBALS[csrf_token]' />",
        "logged_user"     => _currentUser(),
        "server"          => "LibreSoc K+ devel",
        "petrovich"       => (new petrovich\Petrovich),
    ];
}

function route(string $controller, string $action = null, array $params = []): void {
    $controller      = $controller === "" ? "__index__" : $controller;
    $controller_path = SOCN_ROOT."app/handlers/$controller/";
    
    $handler = $controller_path.($action ?? "__index__")."/".mb_strtolower($_SERVER["REQUEST_METHOD"])."_handle.php";
    !file_exists($handler)? $handler = $controller_path.($action ?? "__index__")."/handle.php" : null;

    if(!file_exists($handler)) {
        $static = SOCN_ROOT."app/templates/markdown/$controller/".($action ?? "__index__").".md";
        
        if(!file_exists($static)) {
            $strategy = require(SOCN_ROOT."internals/404_strategy.php");
            $strategy($controller, $action, $params);
            return;
        }
        
        echo((new Parsedown)->text(file_get_contents($static)));
        return;
    }
    
    $controller_meta = new SimpleXMLElement(file_get_contents($controller_path.".meta/controller.xml"));
    require SOCN_ROOT."internals/controller_functions.php";
    $handler         = require($handler);
    $templateParams  = $handler(array_merge_recursive($params, _defaultParams()));
    if($templateParams === false) { #...
            header("HTTP/1.1 205 Reset Content");
            return;
    }
    
    $latte = new Latte\Engine;
    $latte->setTempDirectory(SOCN_ROOT."tmp/templates");
    if(defined("MOBILE")) {
        $template = SOCN_ROOT."app/mobile_templates/$controller/".($action ?? "__index__").".shtml";
        if(!file_exists($template)) $template = SOCN_ROOT."app/templates/$controller/".($action ?? "__index__").".shtml";
    } else {
        $template = SOCN_ROOT."app/templates/$controller/".($action ?? "__index__").".shtml";
    }
    
    $latte->render($template, array_merge_recursive($templateParams, _defaultTemplateParams()));
}
