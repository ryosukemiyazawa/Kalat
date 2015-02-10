<?php
use kalat\SiteConfig;
use kalat\entity\KalatEntry;
use kalat\entity\KalatContent;
use kalat\site\SiteManager;
use kalat\site\SiteHelper;

session_start();
$app->module("session")->checkSession();

$app->bind("get",function($app){ /* @var $app Application */
	
	$result = SiteConfig::loadConfig("site");
	if(!$result){
		header("Location: ". ADMIN_PATH . "create_site");
		exit;
	}
	
	$page = $app->page("Dashboard", "home", "_layout/default");
	
	$page->addLabel("user_name",array(
		"text" => $app->session->getConfig("name")
	));
	
	$buildFile = _SYSTEM_DIR_ . "tmp/build.serialized.txt";
	$buildInfo = (file_exists($buildFile)) ? unserialize(file_get_contents($buildFile)) : array();
	
	$page->addModel("is_built",array(
		"visible" => (isset($buildInfo["time"]) && $buildInfo["time"] > 0)
	));
	$page->addLabel("last_building",array(
		"text" => (isset($buildInfo["time"])) ? date("Y-m-d H:i:s", $buildInfo["time"]) : "--"
	));
	$page->addLabel("build_elapsed",array(
		"text" => (isset($buildInfo["elapsed"])) ? round($buildInfo["elapsed"],4) : "--"
	));
	$page->addLabel("page_count",array(
		"text" => @$buildInfo["pageCount"]
	));
	$page->addLabel("entry_count",array(
		"text" => @$buildInfo["entryCount"]
	));
	
	
});
