<?php
header("X-Powered-By: Libresocial");
define("SOCN_ROOT", dirname(__FILE__)."/../");

require "../vendor/autoload.php";
require "../internals/config.php";
require "../internals/petrovich/Petrovich.php";
require "../internals/event.php";
require "../internals/database.php";
require "../internals/state.php";

require "../internals/route.php";
