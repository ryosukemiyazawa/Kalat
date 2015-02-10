<?php
use kalat\SiteConfig;
use kalat\entity\KalatEntry;
use kalat\entity\KalatContent;
use kalat\site\SiteManager;
use kalat\site\SiteHelper;

$app->module("session")->checkSession();


$app->bind("get",function($app){ /* @var $app Application */
	
	$cacheDir = _SYSTEM_DIR_ . "cache/";
	$files = soy2_scanfiles($cacheDir);
	
	foreach($files as $path){
		@unlink($path);
	}
	
	header("Location:" . ADMIN_PATH);
	exit;
	
});

