<?php
use kalat\SiteConfig;
$app->module("api")->checkToken();

/*
 * get_content.php
 * ファイルをダウンロードする
 *
 */

$app->bind("get",function($app, $args){ /* @var $app Application */
	
	$path = $app->query("path");
	if($path[0] == "."){
		die("invalid");
	}
	
	
	$contentDirectory = SiteConfig::get("content_directory");
	$path = $contentDirectory . $path;
	
	if(file_exists($path) && !is_dir($path)){
		echo file_get_contents($path);
	}
	
	exit;
	
});
