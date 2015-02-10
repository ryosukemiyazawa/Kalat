<?php
namespace kalat\site;

use kalat\site\entry\EntryQuery;
use kalat\SiteConfig;

class SiteHelper{

	private static $_inst;

	public static function instance(){
		if(!self::$_inst){
			self::$_inst = new self();
			
			require __DIR__ . "/functions.php";
			require __DIR__ . "/theme.php";
		}

		return self::$_inst;
	}

	public static function put($key, $value){
		self::instance()->putValue($key, $value);
	}

	public static function get($key, $defValue = null){
		return self::instance()->getValue($key, $defValue);
	}

	public static function clear($key){
		return self::instance()->clearValue($key);
	}
	
	public static function values(){
		$array = &self::instance()->values;
		return $array;
	}
	
	/* internal */
	
	private function __construct(){
		$this->values = new ArrayContainer(array(),\ArrayObject::ARRAY_AS_PROPS);
	}
	
	private function putValue($key,$value){
		$this->values[$key] = $value;
	}

	private function getValue($key,$defValue){
		if(!isset($this->values[$key])){
			return $defValue;
		}
		return $this->values[$key];
	}

	private function clearValue($key){
		unset($this->values[$key]);
	}
	
	/* パーツのインクルード関連 */
	
	private $includeStack = array();
	
	public function doIncludePart($module_name, $args){
		
		//check valid module
		if(strpos($module_name, ".") !== false){
			return null;
		}
		
		//check loop include
		foreach($this->includeStack as $array){
			if($array[0] == $module_name){
				return null;
			}
		}
		
		$path = TEMPLATEDIR . "_part/" . $module_name . ".php";
		if(!file_exists($path))return;
		array_unshift($this->includeStack, [$module_name, $args]);
		
		require $path;
		array_pop($this->includeStack);
	}
	
	public function getIncludeArgument($index, $defValue = null){
		if(isset($this->includeStack[0][1][$index]))return $this->includeStack[0][1][$index];
		return $defValue;
	}
	
	/* Query関連 */
	
	/**
	 * @var EntryQuery
	 */
	private $query;
	
	/**
	 * @return EntryQuery
	 */
	public function prepareQuery(){
		if(!$this->query){
			$this->query = new EntryQuery();
			$this->query->execute();
			
			//結果を構築
			$this->put("entries", $this->query->getResult());
			
			$dir = substr(SiteHelper::get("current_directory"), 1);
			
			$nextPage = $this->query->getNextPage();
			if($nextPage)$this->put("nextPage", _SITE_PUBLIC_PATH_ . $dir . $nextPage);
			
			$previousPage = $this->query->getPreviousPage();
			if($previousPage == 1){
				$this->put("previousPage", _SITE_PUBLIC_PATH_ . $dir);
			}else if($previousPage){
				$this->put("previousPage", _SITE_PUBLIC_PATH_ . $dir . $previousPage);
			}
			
		}
		return $this->query;
	}

}

class ArrayContainer extends \ArrayObject{
	
	public function keys(){
		return array_keys($this->getArrayCopy());
	}
	
	public function offsetGet ($index) {
		if(!isset($this[$index])){
			return false;
		}
		
		return parent::offsetGet($index);
	}
	
	
}