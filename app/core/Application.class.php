<?php

class Application{
	
	private $lamda = array();
	private $_page = null;
	private $_query = array();
	
	public static function getInstance(){
		static $app;
		if(is_null($app)){
			$app = new Application();
			if(isset($_REQUEST))$app->_query = $_REQUEST;
		}
		return $app;
	}
	
	function bind($key, $lamda){
		$this->lamda[$key] = $lamda;
	}
	
	function jump($suffix){
		if(strlen($suffix) && $suffix[0] == "/")$suffix = substr($suffix, 1);
		$url = _APP_URL_ . $suffix;
		header("Location: " . $url);
		exit;
	}
	
	function run($type, $args = null){
		if(isset($this->lamda[$type])){
			$func = $this->lamda[$type];
			
			if(!is_null($args) && is_array($args)){
				return $func($this, $args);
			}else{
				return $func($this);
			}
		}
	}
	
	function query($key){
		return (isset($this->_query[$key])) ? $this->_query[$key] : null;
	}
	
	function setQuery($key, $value){
		$this->_query[$key] = $value;
	}
	
	function execute($args = null){
		
		header("X-APP-PATH: " . $_SERVER["REQUEST_URI"]);
		
		//post時
		$type = strtolower($_SERVER["REQUEST_METHOD"]);
		$this->run($type, $args);
		
		if(!is_null($this->_page)){
			$this->_page->display();
		}
		
	}
	
	function error(){
		
	}
	
	/**
	 * @param unknown_type $template
	 * @param unknown_type $layout
	 * @param unknown_type $args
	 * @return WebPage
	 */
	function page($title, $template, $layout = null, $args = null){
		if(!$args)$args = array();
		$class = "ApplicationPageBase";
		if(class_exists($template)){
			$class = $template;
			$template = $layout;
			$layout = null;
		}
		
		if(is_null($layout) && isset($_SERVER["HTTP_X_REQUESTED_WITH"])
			&& strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
			$layout = "plain";
		}
		
		
		$args["template"] = $template;
		$args["application"] = $this;
		if($layout)$args["layout"] = $layout;
		
		$pageObj = SOY2HTMLFactory::createInstance($class,$args);
		$pageObj->setTitle($title);
		$this->_page = $pageObj;
		
		return $pageObj;
	}
	
	public function module($moduleId, $path = null){
		$module = ModuleManager::module($moduleId, $path);
		$module->setApplication($this);
		$this->$moduleId = $module;
		return $module;
	}
}