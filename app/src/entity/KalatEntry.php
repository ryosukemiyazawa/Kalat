<?php
namespace kalat\entity;

/**
 * @table kalat_entry
 */
class KalatEntry{
	
	/**
	 * @return \kalat\entity\KalatEntryDAO
	 */
	public static function DAO(){
		return \SOY2DAOContainer::get('\kalat\entity\KalatEntryDAO');
	}
	
	public static function TABLE(){
		return "kalat_entry";
	}
	
	public function check(){
		if(!$this->createDate)$this->createDate = time();
		if(!$this->updateDate)$this->updateDate = time();
		
		if(!$this->directory){
			$this->directory = dirname($this->url);
			if($this->directory != "/")$this->directory .= "/";
		}
		
	
		return true;
	}
	
	public function loadContent(){
		$content_path = $this->path . ".html";
		$header_path = $this->path . ".header";
		
		$headers = unserialize(file_get_contents($header_path));
		$this->_headers = $headers;
		
		return file_get_contents($content_path);
	}
	
	public function getContentPath(){
		return $this->path . ".html";
	}
	
	public function getHeaders(){
		return $this->_headers;
	}
	
	public function getAttribute($key,$defValue = null){
		return (isset($this->_headers[$key])) ? $this->_headers[$key] : $defValue;
	}
	
	private $id;
	
	private $title;
	
	private $author;
	
	private $url;
	
	private $directory;
	
	private $collection;
	
	/**
	 * @column content_path
	 */
	private $path;
	
	private $excerptPath;
	
	private $createDate;
	
	private $updateDate;
	
	private $_content;
	
	private $_headers = array();
	
	
	public function setContent($content){
		$this->_content = $content;
	}
	public function getContent(){
		return $this->_content;
	}
	
	
	public function getId(){
		return $this->id;
	}
	public function setId($id){
		$this->id = $id;
		return $this;
	}
	public function getTitle(){
		return $this->title;
	}
	public function setTitle($title){
		$this->title = $title;
		return $this;
	}
	public function getUrl(){
		return $this->url;
	}
	public function setUrl($url){
		$this->url = $url;
		return $this;
	}
	public function getDirectory(){
		return $this->directory;
	}
	public function setDirectory($directory){
		$this->directory = $directory;
		return $this;
	}
	public function getCollection(){
		return $this->collection;
	}
	public function setCollection($collection){
		$this->collection = $collection;
		return $this;
	}
	public function getPath(){
		return $this->path;
	}
	public function setPath($path){
		$this->path = $path;
		return $this;
	}
	public function getCreateDate(){
		return $this->createDate;
	}
	public function setCreateDate($createDate){
		$this->createDate = $createDate;
		return $this;
	}
	public function getUpdateDate(){
		return $this->updateDate;
	}
	public function setUpdateDate($updateDate){
		$this->updateDate = $updateDate;
		return $this;
	}
	public function getExcerptPath(){
		return $this->excerptPath;
	}
	public function setExcerptPath($excerptPath){
		$this->excerptPath = $excerptPath;
		return $this;
	}
	public function getAuthor(){
		return $this->author;
	}
	public function setAuthor($author){
		$this->author = $author;
		return $this;
	}
}


abstract class KalatEntryDAO extends \SOY2DAO{

	public function getDsn() {
		//@TODO entry dbの場所
		return "sqlite:" . _SYSTEM_DIR_ . "tmp/entry.db";
	}

	final public function clear(){
		
		//@TODO entry dbの場所
		$path = _SYSTEM_DIR_ . "tmp/entry.db";
		if(file_exists($path)){
			unlink($path);
		}

		$sql = "create table kalat_entry(" .
				"id INTEGER primary key,".
				"title varchar,".
				"url varchar," .
				"author varchar," .
				"directory varchar," .
				"content_path varchar," .
				"excerpt_path varchar," .
				"collection varchar," .
				"create_date integer,".
				"update_date integer" .
				")";
		$this->executeUpdateQuery($sql);

		$sql = "create index kalat_entry_url on kalat_entry(url);";
		$sql = "create index kalat_entry_author on kalat_entry(author);";
		$sql = "create index kalat_entry_directory on kalat_entry(directory);";
		$sql = "create index kalat_entry_collection on kalat_entry(collection);";
		$this->executeUpdateQuery($sql);
	}

	/**
	 * @return id
	 * @param KalatEntry $entry
	 */
	abstract function insert(KalatEntry $entry);
	abstract function update(KalatEntry $entry);

	/**
	 * @return object
	 * @param string $url
	*/
	abstract function getByUrl($url);
}