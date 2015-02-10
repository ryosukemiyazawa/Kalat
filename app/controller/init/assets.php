<?php
use kalat\SiteConfig;
use kalat\entity\KalatEntry;
use kalat\entity\KalatContent;
use kalat\site\SiteManager;
use kalat\site\SiteHelper;

$app->bind("get",function($app,$args){ /* @var $app Application */
	
	$path = $_GET["path"];
	
	if(strpos($path,"..") !== false)return;
	if($path[0] == "_")return;
	
	$filepath  = THEMEDIR . $path;
	$pathinfo = pathinfo($path);
	
	$extension = $pathinfo["extension"];
	
	$type = "";
	
	switch($extension){
		case "js":
			$type = "text/javascript";
			break;
			
		case "css":
			$type = "text/css";
			break;
		
		case "woff":
			$type = "application/x-font-woff";
			break;
			
		case "ttf":
			$type = "application/x-font-ttf";
			break;
			
		case "jpg":
		case "jpeg":
			$type = "image/jpeg";
			break;
		
		case "png":
			$type = "image/png";
			break;
		
		case "gif":
			$type = "image/gif";
			break;
		
		default:
			$type = null;
	}
	
	if(!$type)return;
	
	if(file_exists($filepath)){
		$contents = file_get_contents($filepath);
		
		header("Content-Type: " . $type);
		$contents = str_replace("url(../", "url(" . TEMPLATEPATH, $contents);
		
		echo $contents;
	}
	
	exit;
	
});