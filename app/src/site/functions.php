<?php
use kalat\site\SiteHelper;
use kalat\entity\KalatEntry;
use kalat\SiteConfig;
use kalat\site\entry\EntryHelper;

function printh($string){
	print(htmlspecialchars($string, ENT_QUOTES));
}

if(!file_exists("dump")){
	function dump(){
		$args = func_get_args();
		echo "<pre>";
		foreach($args as $arg){
			var_dump($arg);
		}
		echo "</pre>";
	}
}
function kalat_site_name(){
	return SiteConfig::get("site.name");
}

function kalat_site_lang(){
	return SiteConfig::get("site.lang");
}

function kalat_get_config($key, $defValue = null){
	return SiteConfig::get($key, $defValue);
}
function kalat_get_value($key, $defValue = null){
	return SiteHelper::get($key, $defValue);
}


/* template include */

function kalat_include(){
	$args = func_get_args();
	$module_name = array_shift($args);
	return SiteHelper::instance()->doIncludePart($module_name, $args);
}

function kalat_get_arg($index = 0, $defValue = null){
	return SiteHelper::instance()->getIncludeArgument($index, $defValue);
}

/**
 * 標準ルール
 * @return boolean
 */
function kalat_have_entries(){
	//have postsはSQLの検索を実行して、$page["entries"]に自動的に入れる
	return SiteHelper::instance()->prepareQuery()->getEntryCount() > 0;
}

function kalat_pagination_format($url){
	if(function_exists("kalat_pagination_format_impl")){
		return kalat_pagination_format_impl($url);
	}
	return array(15, "page-%num.html");
}

function kalat_have_previous_entry($options = array()){
	$entry = SiteHelper::get("entry");
	if(!$entry)return false;
	
	$prevEntry = EntryHelper::findPreviousEntry($entry, $options);
	if($prevEntry){
		SiteHelper::put("previousEntry",_SITE_PUBLIC_PATH_ . substr($prevEntry->getUrl(), 1));
		SiteHelper::put("previousEntry.title", $prevEntry->getTitle());
		return true;
	}
	
	return false;
}
function kalat_have_next_entry($options = array()){
	$entry = SiteHelper::get("entry");
	if(!$entry)return false;
	
	$prevEntry = EntryHelper::findNextEntry($entry, $options);
	if($prevEntry){
		SiteHelper::put("nextEntry",_SITE_PUBLIC_PATH_ . substr($prevEntry->getUrl(), 1));
		SiteHelper::put("nextEntry.title", $prevEntry->getTitle());
		return true;
	}
	
	return false;
}


/* entry */

/**
 * 記事を探すためのスクリプト
 */
function kalat_find_entries($query = null, $limit = null, $offset = null){
	
	if($query){
		list($sql, $binds) = $query;
	}else{
		$sql = "select * from " . KalatEntry::TABLE(). "";
		$binds = array();
	}
	if(!$limit)$limit = 10;
	
	$dao = KalatEntry::DAO();
	if($limit)$dao->setLimit($limit);
	if($offset)$dao->setOffset($offset);
	$res = $dao->executeQuery($sql, $binds);

	$result = array();
	foreach($res as $row){
		$obj = $dao->getObject($row);	/* @var $obj KalatEntry */
		
		if($obj->getExcerptPath()){
			$content = file_get_contents($obj->getExcerptPath());
		}else{
			$content = $obj->loadContent();
		}
		
		$result[] = array(
			"id" => $obj->getId(),
			"title" => $obj->getTitle(),
			"description" => $obj->getAttribute("subtitle"),
			"url" => _SITE_PUBLIC_PATH_ . substr($obj->getUrl(), 1),
			"date" => $obj->getCreateDate(),
			"content" => $content,
			"collection" => $obj->getCollection()
		);
	}

	return $result;
}

/* config */

function kalat_load_configure($confName){
	
	if(strpos($confName, "..") !== false)return array();
	
	$path = _SITE_CONTENT_DIRECTORY_ . "_conf/" . $confName . ".php";
	if(!file_exists($path))return array();
	
	$res = include($path);
	
	return $res;
}

/* short code */

function kalat_add_shortcode($tag, $func){
	
	if(!defined("KALAT_BUILD_MODE") || !KALAT_BUILD_MODE){
		return;
	}
	
	\kalat\builder\SiteBuilder::getCurrentBuilder()->addShortcode($tag, $func);
	
}

