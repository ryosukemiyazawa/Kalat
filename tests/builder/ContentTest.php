<?php

use kalat\builder\SiteBuilder;
use kalat\SiteConfig;
use kalat\entity\KalatContent;

class ContentTest extends PHPUnit_Framework_TestCase {
	
	function setUp(){
		
		SiteConfig::loadConfig("site");
		
	}
	
	function createTemporaryFile($name, $content){
		$path = _SYSTEM_DIR_ . "tmp/" . $name;
		file_put_contents($path, $content);
		
		return $path;
	}
	
	public function testReadHeader(){
		
		$path = $this->createTemporaryFile("test.html", "<p>あいうえお</p>");
		$obj = KalatContent::loadContent($path);
		$this->assertEquals($obj->getType(), KalatContent::TYPE_HTML);
		
		$path = $this->createTemporaryFile("test.md", "ほげほげ");
		$obj = KalatContent::loadContent($path);
		$this->assertEquals($obj->getType(), KalatContent::TYPE_MARKDOWN);
		
		$title = "これがタイトルです";
		$content = <<<CONTENT
/*
 Title: ${title}
 */

#ここからが本文です。

CONTENT;
		$path = $this->createTemporaryFile("test.md", $content);
		$obj = KalatContent::loadContent($path);
		$this->assertEquals($obj->getType(), KalatContent::TYPE_MARKDOWN);
		$this->assertEquals($obj->getAttribute("title"), $title);
		
	}
	
	public function testReadHTMLHeader(){
	
		$title = "これがタイトルです";
		$content = <<<CONTENT
<!--
 Title: ${title}
-->

ここから本文です

CONTENT;
		
		$path = $this->createTemporaryFile("test.html", $content);
		$obj = KalatContent::loadContent($path);
		$this->assertEquals($obj->getType(), KalatContent::TYPE_HTML);
		$this->assertEquals($obj->getAttribute("title"), $title);
	
	}
	
	public function testFixWithHeader(){
		$title = "１行目を自動的にタイトルにするテスト";
		$content_orign = "#${title}\n本文です";
		
		
		$path = $this->createTemporaryFile("test.md", $content_orign);
		$obj = KalatContent::loadContent($path);
		$this->assertEquals($obj->getType(), KalatContent::TYPE_MARKDOWN);
		$this->assertEquals($obj->getAttribute("title"), $title);
		
		//自動変換をかける
		$obj->doFix();

		//自動変換されているか確認
		$content = file_get_contents($path);
		$this->assertEquals(1, preg_match('#/\*(.+)?\*/#s', $content));
		
		$obj = KalatContent::loadContent($path);
		$this->assertEquals($obj->getType(), KalatContent::TYPE_MARKDOWN);
		$this->assertEquals($obj->getAttribute("title"), $title);
		
		//自動変換をかけない場合
		$path = $this->createTemporaryFile("test.md", $content_orign);
		$obj = KalatContent::loadContent($path);
		$this->assertEquals($obj->getType(), KalatContent::TYPE_MARKDOWN);
		$this->assertEquals($obj->getAttribute("title"), $title);
		
		//自動変換されていないことを確認
		$content = file_get_contents($path);
		$this->assertEquals(0, preg_match('#/\*(.+)?\*/#s', $content));
		
	}
	
	public function testFixWithHTMLHeader(){
		$title = "１行目を自動的にタイトルにするテスト";
		$content_orign = "<h1>${title}</h1>\n<p>ほげほげ</p>";
	
		//自動変換をかける
		$path = $this->createTemporaryFile("test.html", $content_orign);
		$obj = KalatContent::loadContent($path);
		$this->assertEquals($obj->getType(), KalatContent::TYPE_HTML);
		$this->assertEquals($obj->getAttribute("title"), $title);
		
		//自動変換をかける
		$obj->doFix();
		$content = file_get_contents($path);
		$this->assertEquals(1, preg_match('#<!--(.+)?-->#s', $content));
		
		$obj = KalatContent::loadContent($path);
		$this->assertEquals($obj->getType(), KalatContent::TYPE_HTML);
		$this->assertEquals($obj->getAttribute("title"), $title);
	}
	
	
	
}