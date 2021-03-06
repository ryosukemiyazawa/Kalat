<?php
use kalat\SiteConfig;
use kalat\builder\SiteBuilder;
use kalat\site\SiteHelper;

$app->module("api")->checkToken();

SiteConfig::loadConfig("site");
SiteConfig::loadConfig("collections");

/*
 * build.php
 * 構築を実行する
 */
$app->bind("get",function($app, $args){ /* @var $app Application */
	
	$builder = new SiteBuilder(SiteConfig::getInstance());
	$builder->setAuthor(SiteConfig::get("user.user_id"));
	
	if(isset($_GET["force"]) && $_GET["force"] == 1){
		$builder->setForceBuild(true);
	}
	
	$builder->build(SiteHelper::instance());
	
	$app->api->result(array());
	
});
