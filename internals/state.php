<?php
use \Firebase\JWT\JWT;

class State
{
    private static function toPayload()
    {
        foreach($GLOBALS["session"] as $key=>$val) {
            if(!is_null($val)) yield $key=>base64_encode(serialize($val));
        }
    }
    
    private static function toState($payload)
    {
        foreach((array) $payload as $key=>$val) $GLOBALS["session"][$key] = unserialize(base64_decode($val));
    }

    static function startSession($long = true)
    {
        $payload = [];
        $token   = JWT::encode($payload, base64_encode(strtr(SOCN_CONFIG["SECRET"], "-_", "+/")), "HS512");
        
        setcookie(
            "state",
            $token,
            $long? time()+60*60*24*30 : null,
            "/",
            null,
            false,
            true
        );
        
        $GLOBALS["session"] = [];
        $_COOKIE["state"]   = $token;
    }
    
    static function initSession($long = true)
    {
        if(!isset($_COOKIE["state"])) self::startSession($long);

        try {
            self::toState(JWT::decode($_COOKIE["state"], base64_encode(strtr(SOCN_CONFIG["SECRET"], "-_", "+/")),[ "HS512"]));
        } catch(Exception $e) {
            self::startSession($long);
            self::initSession($long);
        }
    }
    
    static function get($key, $default = null, $set = false)
    {
        $value = $GLOBALS["session"][md5($key)];
        if(is_null($value)) {
            if($set) self::set($key, $default);
            
            return $default;
        }
        
        return $value;
    }
    
    static function set($key, $value)
    {
        $GLOBALS["session"][md5($key)] = $value;
        $token = JWT::encode(iterator_to_array(self::toPayload()), base64_encode(strtr(SOCN_CONFIG["SECRET"], "-_", "+/")), "HS512");
        setcookie(
            "state",
            $token,
            $long? time()+60*60*24*30 : null,
            "/",
            null,
            false,
            true
        );
        
        return $value;
    }
    
    static function forget($key)
    {
        self::set($key, null);
    }
}
