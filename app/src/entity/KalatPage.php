<?php
namespace kalat\entity;

class KalatPage {
	
	private $title;
	private $url;
	private $content = null;
	private $attachments = array();
	private $type = "dir";
	private $slug = "";
	private $order;
	private $entries = array();
	
	function __construct($url){
		$this->url = $url;
		
		$slug = substr($this->url, 1);
		if($slug[strlen($slug)-1] == "/")$slug = substr($slug, 0, strlen($slug)-1);
		$slug = str_replace("/","-", $slug);
		
		if(strlen($slug) < 1){
			$slug = "index";
		}
		$this->slug = $slug;
		
		$this->title = basename($url);
	}
	
	function addEntry(KalatEntry $entry){
		$this->entries[] = $entry;
	}
	
	function addAttachment($filename, $origin){
		$this->attachments[$filename] = array(
			"name" => $filename,
			"path" => $origin
		);
	}
	function hasContent(){
		return !is_null($this->content);
	}
	function hasAttachment($name){
		if(isset($this->attachments[$name])){
			return true;
		}
		return false;
	}
	
	function getAttachment($name){
		if(isset($this->attachments[$name])){
			return $this->attachments[$name];
		}
		return null;
	}
	
	
	public function getUrl(){
		return $this->url;
	}
	public function setUrl($url){
		$this->url = $url;
		return $this;
	}
	public function getContent(){
		return $this->content;
	}
	public function setContent($content){
		$this->content = $content;
		return $this;
	}
	public function getAttachments(){
		return $this->attachments;
	}
	public function setAttachments($attachments){
		$this->attachments = $attachments;
		return $this;
	}
	public function getType(){
		return $this->type;
	}
	public function setType($type){
		$this->type = $type;
		return $this;
	}
	public function getSlug(){
		return $this->slug;
	}
	public function setSlug($slug){
		$this->slug = $slug;
		return $this;
	}
	public function getEntries(){
		return $this->entries;
	}
	public function setEntries($entries){
		$this->entries = $entries;
		return $this;
	}
	public function getTitle(){
		return $this->title;
	}
	public function setTitle($title){
		$this->title = $title;
		return $this;
	}
	public function getOrder(){
		return $this->order;
	}
	public function setOrder($order){
		$this->order = $order;
		return $this;
	}
	
	
}