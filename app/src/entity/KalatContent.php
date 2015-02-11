<?php
namespace kalat\entity;

/**
 * コンテンツを読み込むためのClass
 * - 追記の処理
 * - ヘッダー部分の解析
 * - ヘッダー付きで書きだす
 */
class KalatContent{
	
	const TYPE_MARKDOWN = "markdown";
	const TYPE_HTML = "html";
	const TYPE_TEXT = "text";
	
	/**
	 * コンテンツを読み込む
	 */
	public static function loadContent($path){
		$obj = new KalatContent($path, null);

		$cachePath = $obj->getCachePath("html");
		$obj->content = file_get_contents($cachePath);
		
		return $obj;
	}
	
	public static $headerProperties = array(
		"title" => "title",
		"subtitle" => "subtitle",
		"author" => "author",
		"slug" => "slug",
		"create" => "create",
		"description" => "description",
		"cover" => "cover",
		"thumbnail" => "thumbnail",
		"meta.og_title" => "meta.og_title",
		"meta.og_description" => "meta.og_description",
		"meta.og_type" => "meta.og_type"
	);
	
	private $type = KalatContent::TYPE_MARKDOWN;
	private $name;
	private $path;	//file absolute path
	private $cachePath;
	private $changed = false;
	private $attributes = array();
	private $content = "";
	private $excerpt = null;
	
	function __construct($path, $cachePath = null){
		
		$pathinfo = pathinfo($path);
		
		switch($pathinfo["extension"]){
			case "md":
				$this->type = KalatContent::TYPE_MARKDOWN;
				break;
			
			case "html":
				$this->type = KalatContent::TYPE_HTML;
				break;
			
			default:
				$this->type = KalatContent::TYPE_TEXT;
		}
		
		$this->name = $pathinfo["filename"];
		
		$this->path = $path;
		
		if(!$cachePath){
			$this->cachePath = $pathinfo["dirname"] . "/" . $pathinfo["filename"];
		}else{
			$this->cachePath = $cachePath;
		}
			
		$headerCacheFile = $this->cachePath . ".header";
		if(!file_exists($headerCacheFile) || !file_exists($path) || filemtime($headerCacheFile) < filemtime($path)){
			$this->changed = true;
		}
		
		if(defined("_KALAT_FORCE_BUILD_") && _KALAT_FORCE_BUILD_){
			$this->changed = true;
		}
		
		//ヘッダーを読み込む
		$this->loadHeader();
	}
	
	public function isChanged(){
		return $this->changed;
	}
	
	public function getCachePath($type = ""){
		if($type){
			return $this->cachePath . "." . $type;
		}
		return $this->cachePath;
	}
	
	private function loadHeader(){
		
		if(!file_exists($this->path)){
			return;
		}
		
		//キャッシュから復元
		if(!$this->changed){
			$headerCachePath = $this->getCachePath("header");
			$this->attributes = (file_exists($headerCachePath)) ? unserialize(file_get_contents($headerCachePath)) : array();
			
			$excerptCachePath = $this->getCachePath("excerpt.html");
			if(file_exists($excerptCachePath)){
				$this->excerpt = file_get_contents($excerptCachePath);
			}
			return;
		}
		
		//ファイルからヘッダーの読み込む
		$headers = array();
		$content_origin = file_get_contents($this->path);
		
		if($this->type == KalatContent::TYPE_MARKDOWN || $this->type == KalatContent::TYPE_TEXT){
			//ヘッダー有り
			if(preg_match('#^/\*(.+)?\*/#s', $content_origin, $tmp)){
				$header_content = explode("\n", $tmp[1]);
				foreach($header_content as $line){
					if(preg_match("#([a-zA-Z]+):(.*)#s", $line, $tmp)){
						$key = $tmp[1];
						$value = $tmp[2];
			
						$headers[strtolower($key)] = trim($value);
					}
				}
				
				//コンテンツ部分だけを抜き出す
				$content_origin = trim(preg_replace('#/\*.+?\*/#s', '', $content_origin, 1));

			//ヘッダー無し
			}else{
				//１行目を取得する
				$first_line = strtok($content_origin, "\n");
				$headers["title"] = preg_replace("/^#+/","", trim($first_line));
				
				//本文は２行目からとする
				$content_origin = substr($content_origin, strlen($first_line));
			}
		}
		
		if($this->type == KalatContent::TYPE_HTML){
			if(preg_match('#^<!--(.+)?-->#s', $content_origin, $tmp)){
				$header_content = explode("\n", $tmp[1]);
				foreach($header_content as $line){
					if(preg_match("#([a-zA-Z]+):(.*)#s", $line, $tmp)){
						$key = $tmp[1];
						$value = $tmp[2];
							
						$headers[strtolower($key)] = trim($value);
					}
				}
				//コンテンツ部分だけを抜き出す
				$content_origin = trim(preg_replace('#<!--.+?-->#s', '', $content_origin, 1));
			//ヘッダー無し
			}else{
				//１行目を取得する
				$first_line = strtok($content_origin, "\n");
				$headers["title"] = strip_tags(trim($first_line));
			
				//本文は２行目からとする
				$content_origin = substr($content_origin, strlen($first_line));
			}
		}
		
		if(!isset($headers["title"]) || !$headers["title"]){
			$headers["title"] = $this->name;
		}
		
		if($this->changed){	//変更があった場合ｈcontentをバックアップする
			if(preg_match('#<!--\smore\s(.*)-->#', $content_origin, $tmp, PREG_OFFSET_CAPTURE)){
				$offset = $tmp[0][1];
				$this->excerpt = trim(substr($content_origin, 0, $offset));
				//@TODO <!-- more -->を何かにリプレースする？
			}
			
			$this->content = $content_origin;
		}
		
		if(!isset($headers["create"])){
			$headers["create"] = date("Y-m-d H:i:s", filectime($this->path));
		}
		if(!isset($headers["update"])){
			$headers["update"] = date("Y-m-d H:i:s", filemtime($this->path));
		}
		
		$this->attributes = $headers;
	}
	
	
	function getAttribute($key, $defValue = null){
		if(!isset($this->attributes[$key]))return $defValue;
		return $this->attributes[$key];
	}
	
	function setAttribute($key, $value){
		$this->attributes[$key] = $value;
	}
	
	/**
	 * ヘッダーが無いファイルを自動的にヘッダーをつけたファイルに変換する
	 */
	function doFix(){
		if(!file_exists($this->path)){
			return;
		}
		if(!$this->content){
			return;
		}
		
		if($this->type == KalatContent::TYPE_HTML){
			$prefix = "<!--";
			$suffix = "-->";
		}else{
			$prefix = "/*";
			$suffix = "*/";
		}
		
		$headers_text = array();
		foreach(self::$headerProperties as $key => $label){
			if(isset($this->attributes[$key])){
				$headers_text[] = "  " . $label . ": " . $this->attributes[$key];
			}
		}
		$headers_text = implode("\n", $headers_text);
		
		$header = $prefix . "\n" . $headers_text . "\n" . $suffix;
		$content = $this->content;
		
		file_put_contents($this->path, $header . "\n" . $content);
	}
	
	/**
	 * 追記があるかどうか
	 */
	function haveMore(){
		return (!is_null($this->excerpt));
	}
	
	/**
	 * 本文を取得する
	 */
	function getContent(){
		return $this->content;
	}
	
	/**
	 * Excerpt
	 */
	function getExcerpt(){
		return $this->excerpt;
	}
	public function getType(){
		return $this->type;
	}
	public function setType($type){
		$this->type = $type;
		return $this;
	}
	public function getPath(){
		return $this->path;
	}
	public function setPath($path){
		$this->path = $path;
		return $this;
	}
	public function getAttributes(){
		return $this->attributes;
	}
	public function setAttributes($attributes){
		$this->attributes = $attributes;
		return $this;
	}
	public function setContent($content){
		$this->content = $content;
		return $this;
	}
	public function setExcerpt($excerpt){
		$this->excerpt = $excerpt;
		return $this;
	}
	
	
	
}

