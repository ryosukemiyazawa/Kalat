<?php
class ApplicationPageBase extends WebPage{
	
	private $app;
	private $_layout = null;
	private $_template = null;
	
	function ApplicationPageBase(){
		$args = func_get_args();
		if(isset($args[0]) && is_array($args[0])){
			SOY2::cast($this, $args[0]);
		}
		
		WebPage::WebPage();
	}
	
	function prepare(){
		parent::prepare();
	}
	
	function build(){
		//customize
	}
	
	function display(){
		parent::display();
	}
	
	function setTemplate($template){
		$this->_template = $template;
	}
	
	function getTemplateFilePath(){
		if(!is_null($this->_template)){
			return SOY2HTMLConfig::TemplateDir() . $this->_template . ".html";
		}
		
		return parent::getTemplateFilePath();
	}
	
	function setLayout($layout){
		$this->_layout = $layout;
	}
	
	function getLayout(){
		if(!is_null($this->_layout))
			return $this->_layout . ".php";
		
		
		return parent::getLayout();
	}
	
	function setApplication($app){
		$this->app = $app;
	}
	
}