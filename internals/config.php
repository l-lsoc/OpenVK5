<?php
class Configuration {
    const REGISTRATION_OPEN   = 1;
    const REGISTRATION_CLOSED = -1;
    const REGISTRATION_INV    = 0;
}

require(SOCN_ROOT."/SocialConfig.php");

define("SOCN_CONFIG", [
    "SITENAME"    => $xSiteName,
    "URL"         => $xSiteURL,

    "DSN"         => $xDatabaseDSN,
    "DB_USER"     => $xDatabaseUser,
    "DB_PASSWORD" => $xDatabasePassword,
    
    "SECRET"      => $xSecret,
    "CAPTCHA_SKD" => $xCaptchaSiteKey,
    "CAPTCHA_KEY" => $xCaptcha,
    "CAPTCHA_IP"  => $xCaptchaVerifyIP,
    "HERE_KEY"    => $xHereKey,
    
    "ADMIN_ID"    => $xAdminOverride ?? 1,
    
    "EXPERIMENTS" => $xExperiments ? $xAllowedExperiments : [],
]);
