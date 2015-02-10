<?php
require "vendor/autoload.php";

define("_SYSTEM_DIR_", dirname(__DIR__) . "/");
define("KALAT_DIRECTORY", dirname(_SYSTEM_DIR_) . "/");

//import enviroment files
require KALAT_DIRECTORY . "conf/env.php";
require KALAT_DIRECTORY . "conf/site.php";

define("APP_MODE","test");

//loader inc
require _SYSTEM_DIR_ . "loader/loader.inc.php";