<?php

use kalat\builder\SiteBuilder;
use kalat\SiteConfig;
class SiteBuilderPageTest extends PHPUnit_Framework_TestCase {
	
	function setUp(){
		
		SiteConfig::loadConfig("site");
		SiteConfig::put("url_suffix","");
		SiteConfig::put("content_directory", realpath("./data/content/"));
	}
	
	private function getDummyFile($path){
		
		$pathinfo = pathinfo($path);
		
		$name = $pathinfo["filename"];
		$ext = $pathinfo["extension"];
		
		$res = array(
			"name" => $name,
			"filename" => basename($path),
			"ext" => $ext,
			"path" => $path,
			"full" => $path,
		);
		
		return $res;
	}
	
	public function testParseEntries(){
		
		$builder = new kalat\builder\SiteBuilder(SiteConfig::getInstance());
		
		$collectionName = "post";
		$collectionConfig = array(
			"name" => "ほげほげ"
		);
		
		$files = array();
		$files[] = $this->getDummyFile("subdir/test.md");
		$files[] = $this->getDummyFile("subdir/index.md");
		
		$builder->buildCollection($collectionName, $collectionConfig, $files);
		
		$this->assertEquals(array_keys($builder->getPages()), array("/post/","/post/subdir/"));
		$this->assertEquals($builder->getPage("/post/")->getTitle(), $collectionConfig["name"]);
		$this->assertEquals(count($builder->getPage("/post/")->getEntries()), 0);
		$this->assertEquals(count($builder->getPage("/post/subdir/")->getEntries()), 1);
		
	}
	
	public function testParsePageWithSlug(){
		
		$builder = new kalat\builder\SiteBuilder(SiteConfig::getInstance());
		
		
		$path = "test.md";
		$file = $this->getDummyFile($path);
		
		$parentUrl = $builder->createParentUrl($path);
		$this->assertEquals("/", $parentUrl);
		
		$page = $builder->parsePage($parentUrl, $file);
		$this->assertEquals("/test/", $page->getUrl());
		
		/* ------- */
		
		$path = "index.md";
		$file = $this->getDummyFile($path);
		
		$parentUrl = $builder->createParentUrl($path);
		$this->assertEquals("/", $parentUrl);
		
		$page = $builder->parsePage($parentUrl, $file);
		$this->assertEquals("/", $page->getUrl());
		
		/* ------- */
		
		$path = "about.md";
		$file = $this->getDummyFile($path);
		$parentUrl = $builder->createParentUrl($path);
		$this->assertEquals("/", $parentUrl);
		
		$page = $builder->parsePage($parentUrl, $file);
		$this->assertEquals("/about/", $page->getUrl());
		
		
	}
	
}