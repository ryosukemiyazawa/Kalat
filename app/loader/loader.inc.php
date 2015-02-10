<?php
require _SYSTEM_DIR_ . "/lib/soy2_build.php";

//include
SOY2::RootDir(_SYSTEM_DIR_);
SOY2::imports("core.*");

SOY2HTMLConfig::CacheDir(_SYSTEM_DIR_ . "cache/");
SOY2DAOConfig::DaoCacheDir(_SYSTEM_DIR_ . "cache/");
SOY2DAOConfig::setOption("connection_failure", "throw");
SOY2HTMLConfig::LayoutDir(_SYSTEM_DIR_ . "template/");

/*
SOY2DAOConfig::user(USER_DB_USER);
SOY2DAOConfig::password(USER_DB_PASSWORD);
if(defined("USER_DB_USER_SLAVE")){
	SOY2DAOConfig::dsn(USER_DB_DNS, "matser");
	SOY2DAOConfig::dsn(USER_DB_DNS_SLAVE, "slave");
	SOY2DAOConfig::setOption("master_slave", true);
}else{
	SOY2DAOConfig::Dsn(USER_DB_DNS);
}
*/

if(defined("DEVELOPING_MODE") && DEVELOPING_MODE){
	define("GENERATE_MODULE", SOY2HTMLConfig::CacheDir());
}

//set path
define("_CONTROLLER_DIR_", _SYSTEM_DIR_ . "controller/" . APP_MODE . "/");
SOY2HTMLConfig::TemplateDir(_SYSTEM_DIR_ . "template/" . APP_MODE . "/");

if(APP_MODE == "init"){
	$cpl_theme_id = "cpl";
	
	$requestUri = $_SERVER["REQUEST_URI"];
	if(strpos($requestUri, "?") !== false)$requestUri = substr($requestUri, 0, strpos($requestUri, "?"));
	$url = $_GET["url"];
	$path = str_replace($url, "", $requestUri);
	$asset_url = $path . "assets/?path=";
	
	define("INIT_SITE_PATH", $path);
	define("THEMEDIR", KALAT_DIRECTORY . "content/theme/");
	define("THEMEPATH", $asset_url);
	
	SOY2HTMLConfig::LayoutDir(THEMEDIR . $cpl_theme_id . "/");
	define("TEMPLATEPATH", THEMEPATH . $cpl_theme_id . "/");
	define("TEMPLATEDIR", THEMEDIR . $cpl_theme_id . "/");

}else if(APP_MODE == "admin"){
	
	$cpl_theme_id = "cpl";
	
	$asset_url = _SITE_PUBLIC_PATH_ . ADMIN_URL . "/assets/?path=";
	
	define("ADMIN_PATH", _SITE_PUBLIC_PATH_ . ADMIN_URL . "/");
	define("THEMEDIR", KALAT_DIRECTORY . "content/theme/");
	define("THEMEPATH", $asset_url);
	
	SOY2HTMLConfig::LayoutDir(THEMEDIR . $cpl_theme_id . "/");
	define("TEMPLATEPATH", THEMEPATH . $cpl_theme_id . "/");
	define("TEMPLATEDIR", THEMEDIR . $cpl_theme_id . "/");
	
	\kalat\SiteConfig::put("content_directory", _SITE_CONTENT_DIRECTORY_);
	\kalat\SiteConfig::put("public_directory", _SITE_PUBLIC_DIRECTORY_);
	
}else{
	\kalat\SiteConfig::put("content_directory", _SITE_CONTENT_DIRECTORY_);
	\kalat\SiteConfig::put("public_directory", _SITE_PUBLIC_DIRECTORY_);
}
