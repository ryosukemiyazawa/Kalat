<?php
use kalat\SiteConfig;
use kalat\entity\KalatEntry;
use kalat\entity\KalatContent;
use kalat\site\SiteManager;
use kalat\site\SiteHelper;
use kalat\site\entry\EntryHelper;

SiteConfig::loadConfig("site");
SiteConfig::loadConfig("collections");
SiteConfig::loadConfig("theme");

$starttime = microtime(true);

$app->bind("display", function($app, $args) use($starttime){
	$templatePath = $args["template"];
	
	
	//set user cookie
	$cookieKey = "_kuser";
	if(!isset($_COOKIE[$cookieKey])){
		$uid = md5(microtime(true) . uniqid());
		setcookie($cookieKey, $uid, time() + 365*24*3600);
		$_COOKIE["_kuser"] = $uid;
	}
	
	//reuireだとセキュアじゃないから
	$func = function($templatePath){
		$page = SiteHelper::values();
		include $templatePath;
	};
	$func($templatePath);
	
	//write to access log
	$access_log = _SYSTEM_DIR_ . "log/" . date("Ymd") . ".log";
	$log_data = array();
	$log_data[] = date("Y-m-d H:i:s");
	$log_data[] = $_COOKIE["_kuser"];
	$log_data[] = $app->query("url");
	$log_data[] = (microtime(true) - $starttime);
	file_put_contents($access_log, implode(" ", $log_data) . "\n", FILE_APPEND);
	
	/*
	$usage = memory_get_peak_usage(true);
	$usage = ($usage / (1024.0 * 1024.0)) . "MB";
	
	$usage2 = memory_get_usage(true);
	$usage2 = ($usage2 / (1024.0 * 1024.0)) . "MB";
	
	//echo "<!-- ";
	//echo date("Y-m-d H:i:s", $last_modified);
	echo '<center><small>';
	echo " " . date("Y-m-d H:i:s");
	echo " " . $usage . " " . $usage2;
	echo " " .  . " sec";
	echo '</small></center>';
	//echo " -->";
	*/
	
	exit;
});

$app->bind("get",function($app, $args) use ($starttime){ /* @var $app Application */
	
	//設定項目の読み込み
	$theme_id = SiteConfig::get("site.theme");
	
	define("THEMEPATH", _SITE_PUBLIC_PATH_ . "theme/");
	
	SOY2HTMLConfig::TemplateDir(THEMEDIR . $theme_id . "/_template/");
	SOY2HTMLConfig::LayoutDir(THEMEDIR . $theme_id . "/");
	define("TEMPLATEPATH", THEMEPATH . $theme_id . "/");
	define("TEMPLATEDIR", THEMEDIR . $theme_id . "/");
	
	//テーマのfunctions.phpを読み込む
	if(file_exists(TEMPLATEDIR  ."_functions.php")){
		include TEMPLATEDIR  ."_functions.php";
	}
	
	//mappingを読み込む
	SiteManager::load();
	
	$url = $app->query("url");
	$urlSuffix = SiteConfig::get("url_suffix", "html");
	
	//リダイレクトしてURLの正規化を行う
	if($url && $url[strlen($url)-1] != "/"){
		$requireJump = false;
		
		if(strpos(basename($url), ".") === false){
			$requireJump = true;
		}
		if(strpos($url, "index.html") !== false){
			$requireJump = true;
			$url = dirname($url);
		}
		
		if($requireJump){
			header("Location: " . _SITE_PUBLIC_URL_ . $url . "/");
		}
	}
	
	//検索用のURLを作る
	$search_url = "/" . $url;
	
	//記事がある場合は記事ページを表示する
	$db = \kalat\entity\KalatEntry::DAO();
	try{
		$entry = $db->getByUrl($search_url);
		$app->run("show_entry", array($entry));
		return;
	}catch(Exception $e){
		
	}
	
	$manager = SiteManager::getInstance();
	$search_url = $manager->checkPaginationURL($search_url);
	$pageConfig = $manager->get($search_url);
	
	if($pageConfig === false){
		$app->run("show_error");
		return;
	}
	
	$slug = $pageConfig["slug"];
	$type = $pageConfig["type"];
	$content = ($pageConfig["content"]) ? KalatContent::loadContent($pageConfig["content"]) : null;
	
	$templateDir = SOY2HTMLConfig::TemplateDir();
	$templatePath = $templateDir . $type . ".php";
	$templateName = $slug . ".php";
	
	if(file_exists($templateDir . $templateName)){
		$templatePath = $templateDir . $templateName;
	}
	
	SiteHelper::put("current_directory", $pageConfig["directory"]);
	SiteHelper::put("page_type", $type);
	SiteHelper::put("current_label", $pageConfig["title"]);
	SiteHelper::put("title", $pageConfig["title"]);
	SiteHelper::put("content", ($content) ? $content->getContent() : null);
	SiteHelper::put("subtitle", ($content) ? $content->getAttribute("subtitle") : null);
	if(isset($pageConfig["order"]))SiteHelper::put("order", $pageConfig["order"]);
	
	//出力
	$app->run("display",["template" => $templatePath]);
	
});

$app->bind("show_entry",function($app, $args){ /* @var $app Application */
	
	/* @var $entry KalatEntry */
	$entry = $args[0];
	
	$content = $entry->loadContent();
	
	//load headers
	
	$type = "entry";
	
	//put values
	SiteHelper::put("current_directory", $entry->getDirectory());
	SiteHelper::put("current_label", $entry->getTitle());
	SiteHelper::put("page_type", $type);
	
	SiteHelper::put("entry", $entry);
	SiteHelper::put("entry.title", $entry->getTitle());
	SiteHelper::put("entry.substitle", $entry->getAttribute("subtitle"));
	SiteHelper::put("entry.content", $content);
	SiteHelper::put("entry.author", $entry->getAuthor());
	SiteHelper::put("entry.date", $entry->getCreateDate());
	
	foreach($entry->getHeaders() as $key => $value){
		SiteHelper::put("entry." . $key, $value);
	}
	
	$templateDir = SOY2HTMLConfig::TemplateDir();
	$templatePath = $templateDir . $type . ".php";
	
	//出力
	$app->run("display",["template" => $templatePath]);

});

$app->bind("show_error",function($app){ /* @var $app Application */

	$content = "<p>page is not found</p>";
	$type = "page";

	//put values
	SiteHelper::put("current_directory", "/");
	SiteHelper::put("current_label", "Error");
	SiteHelper::put("page_type", $type);
	SiteHelper::put("title", "404 not found");
	SiteHelper::put("content", $content);

	$templateDir = SOY2HTMLConfig::TemplateDir();
	$templatePath = $templateDir . $type . ".php";

	//出力
	$app->run("display",["template" => $templatePath]);

});