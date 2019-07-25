<?php
/*
=========== SITE PARAMETERS ===========
| Parameters, related to how website
| appears to users.
=========== ================ ===========
*/
$xSiteName = "OpenVK";
$xSiteURL  = "http://localhost";
$xSiteSkin = NULL;

/*
=========== DATABASE ===========
| Parameters, related to where
| website gets data.
=========== ======== ===========
*/
$xDatabaseDSN      = "sqlite:".SOCN_ROOT."database/openvk-example.db";
$xDatabaseUser     = "";
$xDatabasePassword = "";

/*
=========== SECURITY ===========
| Parameters, related to website
| security subsystem.
=========== ======== ===========
*/
$xSecret           = ""; #random 64-character string, that is known only to you
$xHereKey          = ""; #HERE map key
$xCaptcha          = ""; #hCaptcha key
$xCaptchaSiteKey   = ""; #hCaptcha public key
$xCaptchaVerifyIP  = false; #verify with IP (false if you use CDN/developing on local machine)
/*
=========== OPTIONS ===========
| Parameters, related to how
| website works.
=========== ======= ===========
*/
/*
| Registration parameter.
| Registration may be: opened, closed, or restricted.
| Restricted registration allows to register only via invitecode.
| 
| Allowed values are:
|   Configuration::REGISTRATION_OPEN,
|   Configuration::REGISTRATION_CLOSED,
|   Configuration::REGISTRATION_INV
*/
$xRegistrationOpen = Configuration::REGISTRATION_OPEN;


/*
| Admin override parameter
| By default, user with ID 1 is superuser.
| You can override this behaviour by specifying superuser ID here.
| 
| Allowed values are:
|     Any integer, that is n > 0
*/
$xAdminOverride      = NULL;

$xExperiments        = true; #ATTENTION: very unstable things are called experiments, enable at your own risk!
$xAllowedExperiments = [
    "Admin.ModCP"        #moderation panel, unstable
];
