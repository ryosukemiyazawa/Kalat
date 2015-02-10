<?php

class ModuleManager{
	
	
	public static function instance(){
		static $_inst;
		
		if(!$_inst){
			$_inst = new ModuleManager();
		}
		
		return $_inst;
	}
	
	private $_modules = array();
	
	public static function module($id, $path = null){
		
		$inst = self::instance();
		
		if(is_null($path)){
			
			if(isset($inst->_modules[$id])){
				return $inst->_modules[$id];
			
			}else{
				$path = $id;
			}
		}
		
		$module = (isset($inst->_modules[$id])) ? $inst->_modules[$id] : new Module();
		$module->setModuleId($id);
		
		//モジュールの拡張がある場合
		if(!is_null($path)){
			$module->load($path);
		}
		
		$inst->_modules[$id] = $module;
		return $inst->_modules[$id];
	}
	
}


class Module{
	
	private $application;
	private $moduleId;
	private $_binds = array();
	
	public function load($path){
		
		if(strpos($path, ".php") === false){
			$path .= ".php";
		}
		
		$module = $this;
		require _SYSTEM_DIR_ . "module/" . $path;
		
		if(defined("GENERATE_MODULE")){
			$this->generate($path);
		}
		
	}

	public function getModuleId(){
		return $this->moduleId;
	}

	public function setModuleId($moduleId){
		$this->moduleId = $moduleId;
		return $this;
	}
	
	function set($method, $func){
		$this->_binds[$method] = $func;
		return $this;
	}
	function setApplication(&$app){
		$this->application = $app;
	}
	function getApplication(){
		return $this->application;
	}
	
	function trigger($func, $args){
		$method = $this->_binds[$func];
		return call_user_func_array($method, $args);
	}
	
	function __call($func, $args){
		return $this->trigger($func, $args);
	}
	
	function generate($path){
		$filepath = GENERATE_MODULE . "module_" . $path;
		$modulepath = _SYSTEM_DIR_ . "module/" . $path;
		
		if(file_exists($filepath) && filemtime($filepath) >= filemtime($modulepath)){
			return;
		}
		
		$classname = "module_" . substr(basename($path), 0, strpos(basename($path), "."));
		
		$tmp = array();
		$tmp[] = "<?php /* auto generate module class ".date("Y-m-d H:i:s")."*/";
		$tmp[] = "/* original file:" . $modulepath . " */";
		$tmp[] = "class " . $classname . " {";
		foreach($this->_binds as $method => $func){
			$ref = new ReflectionFunction($func);
			$params = $ref->getParameters();
			
			$method_params = array();
			foreach($params as $a){ /* @var $a ReflectionParameter */
				$line = '$' . $a->getName() . ($a->isDefaultValueAvailable() ? '=\'' . $a->getDefaultValue() . '\'' : '');
				try{
					$class = $a->getClass();
					if($class){
						$line = $class->getName() . " " . $line;
					}
				}catch(Exception $e){
					
				}
				
				$method_params[] = $line;
			}
			
			$tmp[] = "public function " . $method . "(".implode(", ", $method_params)."){}";
		}
		$tmp[] = "}";
		
		file_put_contents($filepath, implode("\n",$tmp));
	}
	
}