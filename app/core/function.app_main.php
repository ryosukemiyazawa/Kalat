<?php

//アプリケーションのメイン
function app_main($action, $base = null){
	
	$app = Application::getInstance();
	$controller = null;
	$args = array();
	
	if(!$action)$action = "home";
	if(strpos($action, "..") !== false)$controller = "error";
	if(strpos($action, "/") !== false){
		$controller = get_controller_path(str_replace("/", "_", $action), $base);
		
		if(!$controller){
			$actions = explode("/", $action);
			
			while(!$controller || !empty($actions)){
				$controller = get_controller_path(implode("/", $actions), $base);
				if($controller)break;
				
				$pop = array_pop($actions);
				array_unshift($args, $pop);
				
				if(empty($actions))break;
			}
		}
		
	}
	
	if(!$controller){
		$controller = get_controller_path($action,$base);
	}
	
	if($controller){
		include($controller);
	}else{
		//error
		include(get_controller_path("default", $base));
	}
	
	try{
		$app->execute($args);
	}catch(Exception $e){
		$app->error();
	}
	
}

function get_controller_path($action, $base = null){
	
	if($base != null){
		$controller = _CONTROLLER_DIR_ . $base . "/" . $action . ".php";
	}else{
		$controller = _CONTROLLER_DIR_ . $action . ".php";
	}
	
	if(file_exists($controller)){
		return $controller;
	}
	return false;
}