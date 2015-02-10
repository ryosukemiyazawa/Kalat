<?php
use kalat\SiteConfig;
use kalat\entity\KalatEntry;
use kalat\entity\KalatContent;
use kalat\site\SiteManager;
use kalat\site\SiteHelper;

session_start();
if($app->module("session")->isLoggedIn()){
	header("Location: ". ADMIN_PATH);
	exit;
}

$app->bind("post",function($app){ /* @var $app Application */

	$userId = $_POST["user_id"];
	$password = $_POST["user_password"];
	
	if(strpos($userId, "..") !== false){
		return $app->run("get");
	}
	
	//load config
	$configurePath = KALAT_DIRECTORY . "conf/user/" . $userId . ".php";
	if(!file_exists($configurePath)){
		return $app->run("get");
	}
	
	$res = include($configurePath);
	
	list($hash_algo, $salt) = explode(".", $res["password"]);
	if(strcmp(hash($hash_algo, $apiKey. "|" . $user["slug"] . "|". $user["password"]), $salt)){
		//OK
		$app->session->login($userId, $res["name"]);
		header("Location: ". ADMIN_PATH);
		exit;
	}
	
	return $app->run("get");

});

$app->bind("get",function($app){ /* @var $app Application */
	
	SiteConfig::loadConfig("site");
	$page = $app->page("Login", "login", "_layout/default");
	
	$page->addInput("input_user_id" ,array(
		"name" => "user_id",
		"value" => @$_POST["user_id"]
	));
	$page->addInput("input_user_password" ,array(
		"name" => "user_password",
		"value" => @$_POST["user_password"]
	));
	
	$page->addModel("error",array(
		"visible" => isset($_POST["user_id"])
	));
	
	
});
