<?php

namespace kalat\site\entry;

use kalat\entity\KalatEntry;
use kalat\SiteConfig;

class EntryHelper{
	
	/**
	 * @return KalatEntry
	 * @param KalatEntry $entry
	 */
	public static function findPreviousEntry(KalatEntry $entry, $options = array()){
		
		list($query,$binds) = self::buildQuery($entry,$options,"desc");
		
		try{
			$res = KalatEntry::DAO()->executeQuery($query, $binds);
		}catch(\Exception $e){
			
		}
		
		if(count($res) < 1)return null;
		
		$obj = KalatEntry::DAO()->getObject($res[0]);
		return $obj;
	}
	
	/**
	 * @return KalatEntry
	 * @param KalatEntry $entry
	 */
	public static function findNextEntry(KalatEntry $entry, $options = array()){
	
		list($query,$binds) = self::buildQuery($entry, $options);
		
		try{
			$res = KalatEntry::DAO()->executeQuery($query, $binds);
		}catch(\Exception $e){
			
		}
		
		if(count($res) < 1)return null;
		
		$obj = KalatEntry::DAO()->getObject($res[0]);
		return $obj;
		
	}
	
	private static function buildQuery(KalatEntry $entry, $options = array(), $order = "asc"){
		
		$order_type = ($order == "asc");
		$order_query_sort = ($order_type) ? "asc" : "desc";
		$compare_type = ($order_type) ? ">" : "<";
		$compare_type_eq = $compare_type . "=";
		$mode_collection = (isset($options["category"]) && $options["category"] == true) ? true : false;
		
		$binds = array();
		
		//
		$where_query = array();
		if($mode_collection){
			$where_query[] = "directory = :directory";
			$binds[":directory"] = $entry->getDirectory();
		}else{
			$where_query[] = "collection = :collection";
			$binds[":collection"] = $entry->getCollection();
		}
		$where_query[] = "id ".$compare_type." :id";
		$binds[":id"] = $entry->getId();
		
		//現在のコレクションの設定を取得する
		$collectionName = $entry->getCollection();
		$orderby = SiteConfig::get("collections." . $collectionName . ".orderby","create");
		
		//並び順
		$order_query = array();
		if($orderby == "create"){
			
			$order_query[] = "create_date " . $order_query_sort;
			$order_query[] = "id " . $order_query_sort;
			$where_query[] = "create_date " . $compare_type_eq .":create_date";
			$binds[":create_date"] = $entry->getCreateDate();
			
		}else if($orderby == "slug"){
			
			$order_query[] = "id " . $order_query_sort;
			$order_query[] = "create_date " . $order_query_sort;
			
		}else{
			
			$order_query[] = "id " . $order_query_sort;
			
		}
		
		//SQLを構築
		$where_query = implode(" AND ", $where_query);
		$order_query = implode(",", $order_query);
		
		$query = "select id,title,url from " . KalatEntry::TABLE() . " where ";
		$query .= $where_query;
		$query .= " order by " . $order_query;
		$query .= " limit 1";
		
		return array($query, $binds);
	}
	
}