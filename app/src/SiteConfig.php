<?php
namespace kalat;

/**
 * サイトの設定情報を保存するためのクラス
 */
class SiteConfig{
	
	private static $_inst;
	private $_configure = array();
	
	public static function put($key, $value){
		return self::getInstance()->putConfigure($key, $value);
	}
	
	public static function get($key, $defValue = null){
		return self::getInstance()->getConfigure($key, $defValue);
	}
	
	public static function loadUserConfig($userName){
		
		$options = array(
			"directory" => KALAT_DIRECTORY . "conf/user/",
			"prefix" => "user"
		);
		self::loadConfig($userName, $options);
	}
	
	public static function loadConfig($configName, $options = array()){
		
		$dir = (isset($options["directory"]))
			? $options["directory"]
			: self::get("content_directory") . "_conf/";
		$prefix = (isset($options["prefix"]))
			? $options["prefix"]
			: $configName;
		
		$path = $dir . $configName . ".php";
		
		if(file_exists($path)){
			
			$instance = self::getInstance();
			$conf = include_once($path);
			
			if(!$conf || !is_array($conf)){
				return true;
			}
			
			$instance->put($prefix, array_keys($conf));
			
			foreach($conf as $key => $value){
				$keyName = $prefix . "." . $key;
				
				if(is_array($value)){
					$keys = array();
					foreach($value as $_key => $_value){
						if(is_numeric($_key)){
							$keys[] = $_value;
							continue;
						}
						$inKeyName = $prefix . "." . $key . "." . $_key;
						$instance->put($inKeyName, $_value);
						$keys[] = $_key;
					}
					$instance->put($keyName, $keys);
				}else{
					$instance->put($keyName, $value);
				}
			}
			
			return $conf;
		}
		
		return false;
	}
	
	public static function getInstance(){
		if(!self::$_inst){
			self::$_inst = new self();
		}
		
		return self::$_inst;
	}
	
	public static function values($prefix = null){
		
		if($prefix){
			$res = array();
			$prefix = $prefix . ".";
			foreach(self::getInstance()->_configure as $key => $value){
				if(strpos($key, $prefix) === false)continue;
				if(is_array($value))continue;
				
				$_key = str_replace($prefix, "", $key);
				$res[$_key] = $value;
			}
			
			return $res;
		}
		
		
		return self::getInstance()->_configure;
	}
	
	
	/* public */
	
	/**
	 * 指定先に書き出す
	 * @param $filepath
	 */
	public function serializeToPath($filepath){
		
	}
	
	public function putConfigure($key, $value){
		$this->_configure[$key] = $value;
	}
	
	public function getConfigure($key, $defValue = null){
		if(isset($this->_configure[$key])){
			return $this->_configure[$key];
		}
		
		return $defValue;
	}
}