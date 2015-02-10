<?php
use kalat\SiteConfig;
use kalat\entity\KalatEntry;
use kalat\entity\KalatContent;
use kalat\site\SiteManager;
use kalat\site\SiteHelper;

session_start();
$app->module("session")->checkSession();

$app->bind("post",function($app){ /* @var $app Application */
	
	$site = $_POST["Site"];
	$errors = array();
	if(strlen(@$site["name"]) < 1)$errors[] = "site.name";
	if(strlen(@$site["lang"]) < 1)$errors[] = "site.lang";
	if(strlen(@$site["theme"]) < 1)$errors[] = "site.theme";
	if(strlen(@$site["style"]) < 1)$errors[] = "site.style";
	
	if(count($errors) < 1){	//OK
		$app->run("doInit",array(
			"site" => $site,
		));
		exit;
	}
	
	$_SESSION["init_site"] = $site;
	
	header("Location:" . ADMIN_PATH . "create_site");
	exit;
	
});

$app->bind("get",function($app){ /* @var $app Application */
	
	$site = array(
		"name" => SiteConfig::get("site.name"),
		"lang" => SiteConfig::get("site.lang"),
		"description" => SiteConfig::get("site.description"),
		"theme" => SiteConfig::get("site.theme"),
		"style" => SiteConfig::get("site.style")
	);
	if(isset($_SESSION["init_site"])){
		$site = $_SESSION["init_site"];
	}
	
	$themes = array(
		"media" => "Media",
		"simple" => "Simple"
	);
	$styles = array(
		"blog" => "Blog",
		"personal" => "Personal",
		"company" => "Company",
		"Document" => "document",
	);
	
	$page = $app->page("Dashboard", "create_site", "_layout/default");
	
	$page->addLabel("user_name",array(
		"text" => $app->session->getConfig("name")
	));
	
	$page->addInput("input_site_name",array(
		"name" => "Site[name]",
		"value" => @$site["name"]
	));
	$page->addInput("input_site_language",array(
		"name" => "Site[lang]",
		"value" => @$site["lang"]
	));
	$page->addTextarea("input_site_description",array(
		"name" => "Site[description]",
		"value" => @$site["description"]
	));
	$page->addSelect("select_theme",array(
		"name" => "Site[theme]",
		"options" => $themes,
		"selected" => @$site["theme"]
	));
	$page->addSelect("select_style",array(
		"name" => "Site[style]",
		"options" => $styles,
		"selected" => @$site["style"]
	));
	
	
});

$app->bind("doInit",function($app,$args){ /* @var $app Application */
	
	unset($_SESSION["init_site"]);
	
	$site = $args["site"];
	
	$scripts = array();
	$configPath = SiteConfig::get("content_directory") . "_conf/site.php";
	
	$scripts[] = "<?php /* site.php generated at " . date("Y-m-d H:i:s") . " */";
	$scripts[] = 'return array(';
	$scripts[] = "\t" . '"name" => "'.addslashes($site["name"]).'",';
	$scripts[] = "\t" . '"description" => "'.addslashes($site["name"]).'",';
	$scripts[] = "\t" . '"lang" => "'.addslashes($site["lang"]).'",';
	$scripts[] = "\t" . '"theme" => "'.addslashes($site["theme"]).'",';
	$scripts[] = ');';
	file_put_contents($configPath, implode("\n", $scripts));
	
	$res = include($configPath);
	
	header("Location:" . ADMIN_PATH);
	exit;
});