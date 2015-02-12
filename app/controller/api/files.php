<?php
use kalat\SiteConfig;

SiteConfig::loadConfig("site");
SiteConfig::loadConfig("collections");

$app->module("api")->checkToken();

/*
 * files.php
 * ファイル一覧を表示する
 *
 */

$app->bind("get",function($app, $args){ /* @var $app Application */
	
	$roles = SiteConfig::get("user.role");
	$haveDeveloperPermission = false;
	$haveWriterPermission = false;
	if(in_array("developer", $roles)){
		$haveDeveloperPermission = true;
	}
	if(in_array("writer", $roles)){
		$haveWriterPermission = true;
	}
	if(in_array("administrator", $roles)){
		$haveDeveloperPermission = true;
		$haveWriterPermission = true;
	}
	
	//collections
	$collections = SiteConfig::get("collections");
	
	
	$manager = new \kalat\api\Manager();
	$files = array();
	
	$targetDir = array();
	if($haveDeveloperPermission){
		$targetDir[] = "_conf";
		$targetDir[] = "theme";
	}
	
	if($haveWriterPermission){
		$targetDir[] = "_page";
		if(is_array($collections)){
			foreach($collections as $collectionName){
				$targetDir[] = "_" . $collectionName;
			}
		}
	}
	
	$files = $manager->getFiles($targetDir);
	
	$app->api->result(array("files" => $files));
	
});
