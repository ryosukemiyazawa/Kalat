<?php
namespace kalat\site;

use kalat\SiteConfig;
class SiteManager{
	
	private static $_inst;
	private $_mapping = array();
	
	public static function load(){
		self::getInstance()->_load();
	}
	
	public static function getInstance(){
		if(!self::$_inst){
			self::$_inst = new self();
		}
	
		return self::$_inst;
	}
	
	public function checkPaginationURL($url){
		
		if(function_exists("kalat_pagination_format_impl")){
			list($pagination, $format) = kalat_pagination_format_impl($url);
			$format = preg_quote($format, "#");
			
			$format = str_replace("%num", '([0-9]+)', $format);
			if(preg_match('#' .$format . '#', basename($url), $tmp)){
				$url = dirname($url);
				if($url[strlen($url)-1] != "/")$url .= "/";
				SiteHelper::put("current_page", (int)$tmp[1]-1);
			}
			
			return $url;
		}
	}
	
	public function get($url){
		
		if(isset($this->_mapping[$url])){
			return $this->_mapping[$url];
		}
	
		return false;
	}
	
	/* private */
	
	private function _load(){
		$tmpDirectory = _SYSTEM_DIR_ . "tmp/";
	
		$contents = file_get_contents($tmpDirectory . "mapping.serialized.txt");
		$this->_mapping = unserialize($contents);
		
		include_once($tmpDirectory . "pagination.php");
	}
	
}