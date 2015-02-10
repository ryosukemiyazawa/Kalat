<?php
namespace kalat\site\entry;

use kalat\entity\KalatEntry;
use kalat\site\SiteHelper;
use kalat\SiteConfig;

/**
 * 記事検索用のクラス
 * SQLを構築する
 */
class EntryQuery{
	
	private $page = 0;
	private $limit = 3;
	private $offset = 0;
	private $format = "page-%num.html";
	
	//結果周り
	private $count = 0;
	private $result = array();
	private $nextPage = null;
	private $previousPage = null;
	
	function __construct(){
		
		SiteConfig::loadConfig("pagenation");
		
		$this->page = SiteHelper::get("current_page");
		$dir = SiteHelper::get("current_directory");
		
		list($limit, $format) = kalat_pagination_format($dir);
		
		$this->limit = $limit;
		$this->format = $format;
		
		$this->offset = $this->page * $this->limit;
		
	}
	
	public function getEntryCount(){
		return count($this->result);
	}
	
	/**
	 * SQLを実行する
	 */
	public final function execute(){
		
		$dir = SiteHelper::get("current_directory");
		
		$order_query = "create_date desc,id desc";
		if(SiteHelper::get("order")){
			$order = SiteHelper::get("order");
			if($order == "slug"){
				$order_query = "id";
			}
		}
		
		if($dir == "/"){
			$query = "select * from " . KalatEntry::TABLE(). " order by " . $order_query;
			$binds = array();
			
			$totalQuery = "select count(*) as count from " . KalatEntry::TABLE();
		}else{
			$query = "select * from " . KalatEntry::TABLE() . " where directory LIKE :dir order by " . $order_query;
			$binds = [":dir" => $dir . "%"];
			
			$totalQuery = "select count(*) as count from " . KalatEntry::TABLE() . " where directory LIKE :dir order by " . $order_query;
		}
		$this->result = kalat_find_entries([$query,$binds], $this->limit, $this->offset);
		
		$res = KalatEntry::DAO()->executeQuery($totalQuery, $binds);
		$this->count = $res[0]["count"];
		$maxPage = ceil($this->count / $this->limit) - 1;
		
		//ページャー
		if($this->page > 0){
			$this->previousPage = str_replace("%num", $this->page, $this->format);
		}
		if($this->page < $maxPage){
			$this->nextPage = str_replace("%num", $this->page + 2, $this->format);
			//$this->nextPage = $this->page + 2;
		}
	}
	
	private function buildQuery(){
		
		
		return array();
	}
	
	/* getter setter */
	
	public function getLimit(){
		return $this->limit;
	}
	public function setLimit($limit){
		$this->limit = $limit;
		return $this;
	}
	public function getOffset(){
		return $this->offset;
	}
	public function setOffset($offset){
		$this->offset = $offset;
		return $this;
	}
	public function getCount(){
		return $this->count;
	}
	public function setCount($count){
		$this->count = $count;
		return $this;
	}
	public function getResult(){
		return $this->result;
	}
	public function setResult($result){
		$this->result = $result;
		return $this;
	}
	public function getPage(){
		return $this->page;
	}
	public function setPage($page){
		$this->page = $page;
		return $this;
	}
	public function getNextPage(){
		return $this->nextPage;
	}
	public function setNextPage($nextPage){
		$this->nextPage = $nextPage;
		return $this;
	}
	public function getPreviousPage(){
		return $this->previousPage;
	}
	public function setPreviousPage($previousPage){
		$this->previousPage = $previousPage;
		return $this;
	}
	
}