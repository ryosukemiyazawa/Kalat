<?php

namespace kalat\builder;

define("KALAT_BUILD_MODE", true);

use kalat\builder\ContentBuilder;
use kalat\entity\KalatEntry;
use kalat\entity\KalatPage;
use kalat\SiteConfig;
use kalat\entity\KalatContent;
use kalat\site\SiteHelper;

/**
 * サイトを構築するためのクラス
 */
class SiteBuilder {
	private static $_instance;
	public static function getCurrentBuilder(){
		return self::$_instance;
	}
	private $collections = array();
	private $contentPath;
	private $publicPath;
	private $cachePath;
	private $contentBuilder;
	private $mapping = array();
	private $urlSuffix = null;
	private $shortCodes = array();
	private $requireAutoFix = false;
	private $author;	//default author
	
	// 作業用のパラメーター
	private $currentPage = null; /* @var $currentPage KalatPage */
	private $currentUrl = null;
	private $attachments = array();
	
	public function __construct(SiteConfig $config){
		$this->contentPath = $config->getConfigure("content_directory");
		$this->publicPath = $config->getConfigure("public_directory");
		$this->collections = $config->getConfigure("collections", array());
		$this->requireAutoFix = $config->getConfigure("content_fix", true);
		$urlSuffix = $config->getConfigure("url_suffix");
		
		$this->contentBuilder = new ContentBuilder();
		
		if(strlen($urlSuffix)>0){
			$this->urlSuffix = ".".$urlSuffix;
		}else{
			$this->urlSuffix = "/";
		}
		
		$this->cachePath = _SYSTEM_DIR_."tmp/";
		
		if(!self::$_instance){
			self::$_instance = $this;
		}
	}
	
	public function setAuthor($author){
		$this->author = $author;
	}
	
	function build(SiteHelper $helper){
		
		$start = microtime(true);
		
		//themeファイルを読み込む
		$themeId = SiteConfig::get("site.theme");
		$path = THEMEDIR . $themeId . "/_functions.php";
		if(file_exists($path)){
			require_once $path;
		}
		
		// 書き出し先
		$tmpDirectory = $this->cachePath;
		
		// 記事の構築を行う
		foreach($this->collections as $collectionName){
			$entryDir = $this->contentPath."_${collectionName}/";
			$files = (file_exists($entryDir)) ? $this->scanDirectory($entryDir) : array();
			
			$collectionConfig = array(
				"slug" => SiteConfig::get("collections.".$collectionName . ".slug"),
				"name" => SiteConfig::get("collections.".$collectionName . ".name"),
				"order" => SiteConfig::get("collections.".$collectionName . ".orderby"),
			);
			$this->buildCollection($collectionName, $collectionConfig, $files);
		}
		
		// ページの構築を行う
		$pageDir = $this->contentPath."_page/";
		$files = $this->scanDirectory($pageDir);
		
		//set index page
		$this->getPage("/")->setTitle("Home");
		
		foreach($files as $file){
			$ext = $file["ext"];
			$isContent = in_array($ext, array("md","txt","html"));
			
			$parentUrl = $this->createParentUrl($file["path"]);
			
			if(!$isContent){
				$this->parseFile($parentUrl, $file);
				continue;
			}
			
			$this->parsePage($parentUrl, $file);
		}
		
		
		//ソートを行う
		uksort($this->mapping, function($a, $b){
			return strcmp($a, $b);
		});
		
		//添付ファイル一覧
		$all_files = array();
		$pageCount = 0;
		$entryCount = 0;
		
		$db = KalatEntry::DAO();
		$db->clear();
		$db->begin();
		
		$mapping = array();
		foreach($this->mapping as $key => $page){	/* @var $page KalatPage */
			
			$path = null;
			
			if($page->hasContent()){
				$path = $this->publishContent($tmpDirectory."_page", $page);
				$pageCount++;
			}
			
			// publish entries
			$entries = $page->getEntries();
			usort($entries, function($a, $b){
				return strcmp($a->getUrl(), $b->getUrl());
			});
			
			$this->currentPage = $page;
			foreach($entries as $entry){
				$this->currentUrl = $entry->getUrl();
				$this->publishEntry($tmpDirectory."_entry", $entry);
				$entryCount++;
			}
			
			$attachments = $page->getAttachments();
			
			if(count($attachments)>0){
				$toPath = substr($page->getUrl(), 1);
				$dirPath = $this->publicPath.$toPath;
				if(!file_exists($dirPath)){
					mkdir($toPath, 0700, true);
				}
				
				foreach($attachments as $file){
					copy($file["path"], $dirPath.$file["name"]);
					$all_files[] = $toPath . $file["name"];
				}
			}
			
			$directory = $page->getUrl();
			if($directory && $directory[strlen($directory)-1]!="/") $directory = dirname($directory);
			if(!$directory) $directory = "/";
			
			$map = array(
				"title" => $page->getTitle(),
				"slug" => $page->getSlug(),
				"url" => $page->getUrl(),
				"directory" => $directory,
				"content" => $path,
				"type" => $page->getType(),
			);
			if($page->getOrder())$map["order"] = $page->getOrder();
			$mapping[$page->getUrl()] = $map;
		}
		$db->commit();
		
		// themeディレクトリを書き出す
		$themeDir = $this->contentPath."theme/";
		$files = $this->scanDirectory($themeDir, function ($name, $depth){
			if($name[0]=="_") return false;
			return true;
		});
		foreach($files as $file){
			$toPath = $this->publicPath."theme/".$file["path"];
			$dir = dirname($toPath);
			if(!file_exists($dir)){
				mkdir($dir, 0700, true);
			}
			copy($file["full"], $toPath);
			
			$all_files[] = "theme/" . $file["path"];
		}
		
		//publicのお掃除
		$files = $this->scanDirectory($this->publicPath, function ($name){
			if($name == "index.php")return false;
			return true;
		});
		foreach($files as $file){
			if(in_array($file["path"], $all_files)){
				//OK
			}else{
				unlink($file["full"]);
			}
		}
		
		// 書き出す
		$toPath = $tmpDirectory."mapping.serialized.txt";
		file_put_contents($toPath, serialize($mapping));
		
		//ページャーの情報を書き出す
		SiteConfig::loadConfig("pagination");
		$res = SiteConfig::values("pagination");
		
		$paginationCount = (isset($res["pagination"])) ? @$res["pagination"] : 10;
		$paginationFormat = (isset($res["format"])) ? @$res["format"] : "page-%num.html";
		
		$scripts = array();
		$scripts[] = '<?php function kalat_pagination_format_impl($url){ ';
		$scripts[] = '  $pagination = ' . (int)($paginationCount) . ';';
		$scripts[] = '  $format = "' . addslashes($paginationFormat) . '";';
		
		foreach($res as $key => $value){
			
			if(strpos($key, ".format") !== false){
				$key = str_replace(".format","",$key);
				$scripts[] = '  if(strpos($url, "/'.addslashes($key).'/") !== false){';
				$scripts[] = '    $format = "' . addslashes($value) . '";';
				$scripts[] = '  }';
			}
			
			if(strpos($key, ".pagination") !== false){
				$key = str_replace(".pagination","",$key);
				$scripts[] = '  if(strpos($url, "/'.addslashes($key).'/") !== false){';
				$scripts[] = '    $pagination = ' . (int)($value) . ';';
				$scripts[] = '  }';
			}
		}
		$scripts[] = '  return array($pagination, $format);';
		$scripts[] = '}';
		
		// 書き出す
		$toPath = $tmpDirectory."pagination.php";
		file_put_contents($toPath, implode("\n", $scripts));
		
		//ビルド情報を書き出す
		$buildInfo = array();
		$buildInfo["time"] = time();
		$buildInfo["elapsed"] = microtime(true) - $start;
		$buildInfo["pageCount"] = $pageCount;
		$buildInfo["entryCount"] = $entryCount;
		
		$toPath = $tmpDirectory."build.serialized.txt";
		file_put_contents($toPath, serialize($buildInfo));
		
	}
	function buildCollection($collectionName, $collectionConfig, $files){
		$collectionSlug = (isset($collectionConfig["slug"])) ? $collectionConfig["slug"] : $collectionName;
		$collectionTitle = (isset($collectionConfig["name"])) ? $collectionConfig["name"] : $collectionName;
		$collectionOrder = (isset($collectionConfig["order"])) ? $collectionConfig["order"] : null;
		
		//タイトルを自動的に入れる
		$this->getDirectory("/" . $collectionSlug)
			->setTitle($collectionTitle)
			->setOrder($collectionOrder);
		
		foreach($files as $file){
			$ext = $file["ext"];
			$isContent = in_array($ext, array(
				"md",
				"txt",
				"html"
			));
			$parentUrl = $this->createParentUrl($file["path"], $collectionSlug);
			$this->getDirectory($parentUrl)->setOrder($collectionOrder);
			
			if(!$isContent){
				$this->parseFile($parentUrl, $file);
				continue;
			}
			
			if($file["name"]=="index"){
				$this->parsePage($parentUrl, $file);
				continue;
			}
			
			$this->parseEntry($parentUrl, $collectionName, $collectionConfig, $file);
		}
	}
	function parseFile($parentUrl, $file){
		$filename = $file["filename"];
		$fullpath = $file["full"];
		
		$this->getDirectory($parentUrl)->addAttachment($filename, $fullpath);
		
		$url = $parentUrl . "/" . $filename;
		$this->attachments[] = $url;
	}
	
	/**
	 * 記事を一件処理する
	 *
	 * @param unknown $collectionName
	 * @param unknown $collectionConfig
	 * @param unknown $file
	 * @return KalatEntry
	 */
	public function parseEntry($parentUrl, $collectionName, $collectionConfig, $file){
		
		$fullpath = $file["full"];
		$path = $file["path"];
		
		$url = $this->createContentUrl($parentUrl, $file["name"]);
		
		$cachePath = $this->cachePath . "_entry" . $url;
		if($cachePath[strlen($cachePath)-1]=="/"){
			$cachePath .= "index";
		}
		
		//コンテンツを作成
		$content = new KalatContent($fullpath, $cachePath);
		if($content->getAttribute("slug")){
			$url = $this->createContentUrl($parentUrl, $content->getAttribute("slug"));
		}
		
		$entry = new KalatEntry();
		$entry->setTitle($content->getAttribute("title"));
		$entry->setUrl($url);
		$entry->setPath($fullpath);
		$entry->setCollection($collectionName);
		$entry->setContent($content);
		
		if($content->getAttribute("create")){
			$entry->setCreateDate(strtotime($content->getAttribute("create")));
			
		}
		if($content->getAttribute("update")){
			$entry->setUpdateDate(strtotime($content->getAttribute("update")));
		}
		$entry->check();
		
		if($content->isChanged() && $this->requireAutoFix){
			$content->doFix();
		}
		
		$this->getDirectory($parentUrl, "archive")->addEntry($entry, $content);
		
		return $entry;
	}
	function parsePage($parentUrl, $file){
		$fullpath = $file["full"];
		$path = $file["path"];
		
		$url = $this->createContentUrl($parentUrl, $file["name"]);
		
		$cachePath = $this->cachePath . "_page" . $url;
		if($cachePath[strlen($cachePath)-1]=="/"){
			$cachePath .= "index";
		}
		
		$content = new KalatContent($fullpath, $cachePath);
		
		if($content->getAttribute("slug")){
			$url = $this->createContentUrl($parentUrl, $content->getAttribute("slug"));
		}
		
		if($content->isChanged() && $this->requireAutoFix){
			$content->doFix();
		}
		
		return $this->getPage($url)->setTitle($content->getAttribute("title"))->setContent($content);
	}
	
	/**
	 * ページを書き出す
	 *
	 * @param unknown $directory
	 * @param KalatPage $page
	 * @return string
	 */
	function publishContent($directory, KalatPage $page){
		
		/* @var $content KalatContent */
		$content = $page->getContent();
		
		$htmlCachePath = $content->getCachePath("html");
			
		//変更がある場合は書き出しを行う
		if($content->isChanged()){
		
			$headerCachePath = $content->getCachePath("header");
			$excerptCachePath = $content->getCachePath("excerpt.html");
		
			if(!file_exists(dirname($htmlCachePath))){
				mkdir(dirname($htmlCachePath), 0700, true);
			}
		
			//ヘッダーを書き出す
			file_put_contents($headerCachePath, serialize($content->getAttributes()));
		
			//本文を書き出す
			$html = $this->contentBuilder->buildContent($content->getType(), $content->getContent());
			file_put_contents($htmlCachePath, $html);
		
			if($content->haveMore()){
				$html = $this->contentBuilder->buildContent($content->getType(), $content->getExcerpt());
				file_put_contents($excerptCachePath, $html);
			}
		}
		
		return $htmlCachePath;
	}
	
	/**
	 * 記事を書き出す
	 *
	 * @param unknown $directory
	 * @param KalatEntry $entry
	 */
	function publishEntry($directory, KalatEntry $entry){
		if($entry->check()){
			
			/* @var $content KalatContent */
			$content = $entry->getContent();
			$htmlCachePath = $content->getCachePath("html");
			$excerptCachePath = $content->getCachePath("excerpt.html");
			
			//変更がある場合は書き出しを行う
			if($content->isChanged()){
				
				$headerCachePath = $content->getCachePath("header");
				
				if(!file_exists(dirname($htmlCachePath))){
					mkdir(dirname($htmlCachePath), 0700, true);
				}
				
				//ヘッダーを書き出す
				if($content->getAttribute("cover")){
					$content->setAttribute("cover", $this->getImageUrl($content->getAttribute("cover")));
				}
				file_put_contents($headerCachePath, serialize($content->getAttributes()));
				
				//本文を書き出す
				$html = $this->contentBuilder->buildContent($content->getType(), $content->getContent());
				file_put_contents($htmlCachePath, $html);
				
				if($content->haveMore()){
					$html = $this->contentBuilder->buildContent($content->getType(), $content->getExcerpt());
					file_put_contents($excerptCachePath, $html);
					
					//moreがある時
					$entry->setExcerptPath($excerptCachePath);
				}
			}
			
			
			$entry->setPath($content->getCachePath());
			if($content->haveMore())$entry->setExcerptPath($excerptCachePath);
			
			//author情報が無い時はログインユーザーとする
			if(is_null($entry->getAuthor())){
				$entry->setAuthor($this->author);
			}
			
			$id = KalatEntry::DAO()->insert($entry);
		}
	}
	
	/* internal methods */
	
	/**
	 * 全てのページを取得
	 *
	 * @return multitype:KalatPage
	 */
	public function getPages(){
		return $this->mapping;
	}
	
	/**
	 *
	 * @return KalatPage
	 */
	public function getPage($url, $type = "page"){
		if($url=="/") $type = "index"; // index is front page
		
		if(!isset($this->mapping[$url])){
			$this->mapping[$url] = new KalatPage($url);
			$this->mapping[$url]->setType($type);
		}
		
		return $this->mapping[$url];
	}
	
	public function getDirectory($url){
		
		if($url == "//"){
			$url = "/";
		}
		
		if($url[strlen($url)-1] != "/")$url .= "/";
		
		return $this->getPage($url, "archive");
	}
	
	/**
	 * 相対パスからURLを作成する
	 *
	 * @param unknown $path
	 * @param prefix $suffix
	 * @return string
	 */
	public function createParentUrl($path, $prefix = null){
		$url = dirname($path);
		
		$urls = array();
		if($url=="."){
			//do nothing
		}else{
			$urls[] = $url;
		}
		
		if($prefix){
			return $this->joinURL("/" . $prefix, $urls);
		}
		
		return "/" . $this->joinURL($urls);
	}
	
	/**
	 * URLを組み立てる
	 *
	 * @param unknown $parentUrl
	 * @param unknown $filename
	 * @return Ambigous <string, unknown>
	 */
	public function createContentUrl($parentUrl, $filename){
		if($filename=="index"){
			$name = "";
		}else{
			$name = $filename;
			$name .= $this->urlSuffix;
		}
		
		if($filename[0]=="_"){
			$url = $parentUrl.$this->urlSuffix;
		}else if($parentUrl=="/"){
			$url = $parentUrl.$name;
		}else if($filename=="index"){
			$url = $parentUrl . "/";
		}else{
			$url = $parentUrl."/".$name;
		}
		
		return $url;
	}
	
	/**
	 *
	 * @param string $path
	 * @param function $rule
	 * @param string $origin
	 * @param number $depth
	 * @return array
	 */
	private function scanDirectory($path, $rule = null, $origin = "", $depth = 0){
		$res = array();
		$files = scandir($path);
		$childDepth = $depth+1;
		
		foreach($files as $file){
			if($file[0]==".") continue;
			
			// フィルタリングを行う
			if($rule&&!$rule($file, $depth)) continue;
			
			$fullPath = $path.$file;
			$filePath = $origin.$file;
			if(is_dir($fullPath)){
				$child_res = $this->scanDirectory($fullPath.DIRECTORY_SEPARATOR, $rule, $filePath.DIRECTORY_SEPARATOR, $childDepth);
				
				$res = array_merge($res, $child_res);
			}else{
				$pathinfo = pathinfo($fullPath);
				
				$name = $pathinfo["filename"];
				$ext = $pathinfo["extension"];
				
				$res[] = array(
					"name" => $name,
					"filename" => $file,
					"ext" => $ext,
					"path" => $filePath,
					"full" => $fullPath
				);
			}
		}
		
		if($depth==0){
			sort($res);
		}
		
		return $res;
	}
	
	/* short code */
	
	public function addShortCode($tag, $func){
		$this->shortCodes[$tag] = $func;
	}
	public function getShortCodes(){
		return $this->shortCodes;
	}
	public function getImageUrl($url){
		
		if(strlen($url)<1){
			return "";
		}
		
		if($url[0]=="/"){
			return $url;
		}
		
		if($this->currentPage && $this->currentPage->hasAttachment($url)){
			$dirUrl = $this->currentPage->getUrl();
			$url = _SITE_PUBLIC_PATH_.substr($dirUrl,1).$url;
			return $url;
		}
		
		if($this->currentPage){
			$tmpUrl = $this->currentPage->getUrl() . $url;
			
			if(in_array($tmpUrl, $this->attachments)){
				return _SITE_PUBLIC_PATH_ . substr($tmpUrl,1);
			}
			
			$tmpUrl = $this->currentUrl . $url;
			if(in_array($tmpUrl, $this->attachments)){
				return _SITE_PUBLIC_PATH_ . substr($tmpUrl,1);
			}
		}
		
		return $url;
	}
	
	public function joinURL(){
		$args = func_get_args();
		$res = array();
		foreach($args as $arg){
			if(is_array($arg)){
				$res = array_merge($res, $arg);
			}else{
				$res[] = $arg;
			}
		}
		
		return str_replace(array("///","//"), "/", implode("/", $res));
	}
}