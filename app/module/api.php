<?php
use kalat\SiteConfig;
/* @var $module Module */

/*
 * 1	error
 * 2	invalid token
 * 90	unknown error
 */

$module->set("_tickStart", function() use ($module){
	$module->_time = microtime(true);
});

$module->set("error",function($message, $code = null) use ($module){
	
	if(!$code){
		$code = 1;	//default error
	}
	if(!isset($message)){ $message = "error"; }
	
	$process_time = microtime(true) - $module->_time;
	
	header("Content-Type: application/json; charset=UTF-8");
	
	$result = array();
	$result["success"] = 0;
	$result["time"] = time();
	$result["process"] = $process_time;
	
	$result["error"] = array(
		"code" => $code,
		"message" => $message
	);
	
	echo json_encode($result, JSON_UNESCAPED_UNICODE);
	
});

$module->set("result",function($result) use ($module){
	
	$process_time = microtime(true) - $module->_time;
	
	header("Content-Type: application/json; charset=UTF-8");
	
	$result["success"] = 1;
	$result["time"] = time();
	$result["process"] = $process_time;
	
	echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	
});


$module->set("checkToken",function() use ($module){

	if(!isset($_SERVER["HTTP_KALATTOKEN"])){
		$module->error("invalid access token", 2);
		return;
	}
	
	$token = $_SERVER["HTTP_KALATTOKEN"];
	$res = base64_decode($token);
	$res = json_decode($res, true);
	$time = $res["time"];
	$user = $res["slug"];
	list($algo, $token_string) = explode(".", $res["token"]);
	
	SiteConfig::loadUserConfig($user);
	
	//check
	$password = SiteConfig::get("user.password");
	$auth_key = md5(API_KEY . "|" . $password);
	$require = hash($algo, $auth_key . "|" . $time);
	
	if(strcmp($require, $token_string) !== 0){
		$module->error("invalid access token", 2);
	}

});


$module->_tickStart();