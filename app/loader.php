<?php
define("_SYSTEM_DIR_", __DIR__ . "/");
define("KALAT_DIRECTORY", dirname(__DIR__) . "/");
define("KALAT_VERSION", "0.01");

//初期化済みかどうか
if(!file_exists(KALAT_DIRECTORY . "conf/env.php")){
	define("APP_MODE","init");	//初期化
	
	require _SYSTEM_DIR_ . "loader/loader.inc.php";
	app_main($url);
	return;
}

/* 設定項目の読み込み */
include KALAT_DIRECTORY . "conf/env.php";
include KALAT_DIRECTORY . "conf/site.php";
if(file_exists(KALAT_DIRECTORY . "conf/dev.php")){
	include KALAT_DIRECTORY . "conf/dev.php";
}else{
	define("DEVELOPING_MODE", false);
}

$url = (isset($_GET["url"])) ? $_GET["url"] : "";

//do cache
require _SYSTEM_DIR_ . "loader/cache.inc.php";

if(strpos($url, ADMIN_API_URL) === 0){
	define("APP_MODE","api");
	$url = str_replace(ADMIN_API_URL, "", $url);
}else if(strpos($url, ADMIN_URL) === 0){
	define("APP_MODE","admin");
	$url = str_replace(ADMIN_URL, "", $url);
}else{
	define("APP_MODE","site");
}

//loader inc
require _SYSTEM_DIR_ . "loader/loader.inc.php";

app_main($url);

