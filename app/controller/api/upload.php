<?php
use kalat\SiteConfig;
$app->module("api")->checkToken();

/*
 * upload.php
 * ファイルを更新する
 *
 */
$app->bind("get",function($app, $args){ /* @var $app Application */
	$app->api->error("invalid!");
});

$app->bind("post",function($app, $args){ /* @var $app Application */
	
	$path = $app->query("path");
	$content = $app->query("content");
	$time = $app->query("time");
	
	if($path[0] == "."){
		die("invalid");
	}
	
	
	$contentDirectory = SiteConfig::get("content_directory");
	$path = $contentDirectory . $path;
	
	$dir = dirname($path);
	if(!file_exists($dir)){
		mkdir($dir, _KALAT_DIRECTORY_PERMISSON_, true);
	}
	
	echo file_put_contents($path, base64_decode($content));
	
	if($time){
		touch($path, $time);
	}
	
	exit;
	
});
