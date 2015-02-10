<?php

use kalat\builder\SiteBuilder;
use kalat\SiteConfig;
class SiteBuilderTest extends PHPUnit_Framework_TestCase {
	
	function setUp(){
		
		SiteConfig::loadConfig("site");
		
	}
	
	public function testParseEntryWithSlug(){
		
		SiteConfig::put("url_suffix","");
		$builder = new kalat\builder\SiteBuilder(SiteConfig::getInstance());
		
		$collectionName = "hoge";
		$slug = "test";
		$path = "hoge/_hoge";
		
		$collectionConfig = array(
			"slug" => $slug
		);
		$file = array(
			"name" => "_hoge",
			"full" => "dummy full path",
			"path" => $path
		);
		
		$parentUrl = $builder->createParentUrl($path, $collectionConfig["slug"]);
		$this->assertEquals("/test/hoge", $parentUrl);
		
		$contentUrl = $builder->createContentUrl($parentUrl, $file["name"]);
		$this->assertEquals("/test/hoge/", $contentUrl);
		
		
		$collectionName = "hoge";
		$slug = "test";
		$path = "hoge/fuga.md";
		
		$collectionConfig = array(
			"slug" => $slug
		);
		$file = array(
			"name" => "fuga",
			"full" => "dummy full path",
			"path" => $path
		);
		
		$parentUrl = $builder->createParentUrl($path, $collectionConfig["slug"]);
		$this->assertEquals("/test/hoge", $parentUrl);
		
		$contentUrl = $builder->createContentUrl($parentUrl, $file["name"]);
		$this->assertEquals("/test/hoge/fuga/", $contentUrl);
		
		
		$parentUrl = $builder->createParentUrl("2015-02-06_test.md", "post");
		$this->assertEquals("/post", $parentUrl);
		
		
		$collectionName = "hoge";
		$slug = "/";
		$path = "hoge/_hoge.md";
		
		$collectionConfig = array(
			"slug" => $slug
		);
		$file = array(
			"name" => "_hoge",
			"full" => "dummy full path",
			"path" => $path
		);
		
		$parentUrl = $builder->createParentUrl($path, $collectionConfig["slug"]);
		$this->assertEquals("/hoge", $parentUrl);
		
		$contentUrl = $builder->createContentUrl($parentUrl, $file["name"]);
		$this->assertEquals("/hoge/", $contentUrl);
		
		
	}
	
	public function testParseEntryWithNoSlug(){
		
		SiteConfig::put("url_suffix","");
		$builder = new kalat\builder\SiteBuilder(SiteConfig::getInstance());
		
		$collectionName = "news";
		$path = "hoge/_hoge";
	
		$collectionConfig = array(
			
		);
		$file = array(
			"name" => "_hoge",
			"full" => "dummy full path",
			"path" => $path
		);
	
		$parentUrl = $builder->createParentUrl($path, $collectionName);
		$this->assertEquals("/news/hoge", $parentUrl);
	
		$contentUrl = $builder->createContentUrl($parentUrl, $file["name"]);
		$this->assertEquals("/news/hoge/", $contentUrl);
	
	
		$slug = "test";
		$path = "hoge/fuga.md";
		
		$file = array(
			"name" => "fuga",
			"full" => "dummy full path",
			"path" => $path
		);
	
		$parentUrl = $builder->createParentUrl($path, $collectionName);
		$this->assertEquals("/news/hoge", $parentUrl);
	
		$contentUrl = $builder->createContentUrl($parentUrl, $file["name"]);
		$this->assertEquals("/news/hoge/fuga/", $contentUrl);
	
	}
	
	public function testParseEntryWithUrlSuffix(){
		
		//設定を上書き
		SiteConfig::put("url_suffix", "html");
		$builder = new kalat\builder\SiteBuilder(SiteConfig::getInstance());
		
		$collectionName = "news";
		$path = "hoge/_hoge";
		
		$collectionConfig = array(
				
		);
		$file = array(
			"name" => "_hoge",
			"full" => "dummy full path",
			"path" => $path
		);
		
		$parentUrl = $builder->createParentUrl($path, $collectionName);
		$this->assertEquals("/news/hoge", $parentUrl);
		
		$contentUrl = $builder->createContentUrl($parentUrl, $file["name"]);
		$this->assertEquals("/news/hoge.html", $contentUrl);
		
		
		$slug = "test";
		$path = "hoge/fuga.md";
		
		$file = array(
			"name" => "fuga",
			"full" => "dummy full path",
			"path" => $path
		);
		
		$parentUrl = $builder->createParentUrl($path, $collectionName);
		$this->assertEquals("/news/hoge", $parentUrl);
		
		$contentUrl = $builder->createContentUrl($parentUrl, $file["name"]);
		$this->assertEquals("/news/hoge/fuga.html", $contentUrl);
		
	}
	
}