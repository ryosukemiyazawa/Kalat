<?php
/* @var $module Module */
@session_start();

$module->set("login",function($slug, $name) use ($module){
	
	$config = include(KALAT_DIRECTORY . "conf/user/" . $slug . ".php");
	
	$_SESSION["kalat_auth_login"] = array(
		"id" => $slug,
		"name" => $name,
		"config" => $config
	);
	
});

$module->set("logout",function($slug) use ($module){

	unset($_SESSION["kalat_auth_login"]);

});

$module->set("isLoggedIn",function() use ($module){
	
	if(!isset($_SESSION["kalat_auth_login"])){
		return false;
	}
	return true;
	
});

$module->set("checkSession",function() use ($module){

	if(!isset($_SESSION["kalat_auth_login"])){
		header("Location: ". ADMIN_PATH . "login");
		exit;
	}
	return $_SESSION["kalat_auth_login"];

});

$module->set("getSlug",function() use ($module){

	return $_SESSION["kalat_auth_login"]["id"];

});

$module->set("getConfig",function($key, $defValue = null) use ($module){

	if(isset($_SESSION["kalat_auth_login"]["config"][$key])){
		return $_SESSION["kalat_auth_login"]["config"][$key];
	}
	
	return $defValue;

});