<?php
ini_alter("geoip.custom_directory", SOCN_ROOT."/geoip");

function getIpByUser(int $id): ?string
{
    $db     = open_database();
    $users  = $db->table("users");
    $user   = $users->get($id);
    if(!$user) return null;

    return $user->registering_ip;
}

return (function($args) {
    register_experiment("Admin.ModCP");
    assert_user($args);
    assert_su($args);
    $db     = open_database();
    $users  = $db->table("users");
    $tokens = $db->table("tokens");
    $ip     = $_POST["ip"] ?? getIpByUser($_POST["user"] ?? $args["__user__"]->id);

    $genericInfo = false;
    if(extension_loaded("geoip")) {
        $genericInfo = geoip_record_by_name($ip);
    }

    $registrations = $users->where("registering_ip", $ip);
    $sessions      = $tokens->where("ip", $ip);

    return [
        "ip"       => $ip,
        "generic"  => (object) $genericInfo,
        "users"    => $registrations,
        "sessions" => $sessions,
        "key"      => SOCN_CONFIG["HERE_KEY"],
    ];
});
