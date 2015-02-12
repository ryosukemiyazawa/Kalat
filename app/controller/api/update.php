<?php
use kalat\SiteConfig;
$app->module("api")->checkToken();

/*
 * update.php
 * バージョンを上げる
 *
 */
$app->bind("get",function($app, $args){ /* @var $app Application */
	$app->api->error("invalid!");
});

$app->bind("post",function($app, $args){ /* @var $app Application */
	
	$file = $_FILES["package"];
	$extractPath = _SYSTEM_DIR_ . "tmp/zip/";
	$localpath = KALAT_DIRECTORY;
	
	$zip = new ZipArchive();
	if($zip->open($file["tmp_name"]) !== true){
		$app->api->error("invalid package");
		return;
	}
	
	$zip->extractTo($extractPath);
	$zip->close();
	
	$files = scandir($extractPath);
	
	$versionInfoFile = $extractPath . "version";
	if(!file_exists($versionInfoFile)){
		$app->api->error("invalid package: require version info");
		return;
	}
	
	$lines = file($versionInfoFile);
	$version = $lines[0];
	
	$result = array();
	$result[] = "update to " . $version;
	
	for($i=2;$i<count($lines);$i++){
		$line = trim($lines[$i]);
		$path = $extractPath . $line;
		$toPath = $localpath . $line;
		
		//check version compare
		if(filesize($path) < 1){
			continue;
		}
		
		//check filesize
		if(file_exists($toPath) && filesize($path) == filesize($toPath)){
			continue;
		}
		
		if(defined("DEVELOPING_MODE") && DEVELOPING_MODE){
			//skip
		}else{
			copy($path, $toPath);
			$result[] = "update " . $line;
		}
	}
	
	$app->api->result(["log" => implode("\n", $result)]);
	
});
