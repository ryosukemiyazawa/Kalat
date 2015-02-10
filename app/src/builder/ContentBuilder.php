<?php
namespace kalat\builder;

use kalat\builder\parser\MarkdownParser;
use kalat\entity\KalatContent;
use kalat\builder\parser\TextParser;
use kalat\builder\parser\HTMLParser;

/**
 * コンテンツ構築のためのクラス
 */
class ContentBuilder{
	
	/**
	 * コンテンツを構築する
	 */
	function buildContent($type, $text){
		if($type == KalatContent::TYPE_MARKDOWN){
			return MarkdownParser::convert($text);
		}
		
		if($type == KalatContent::TYPE_TEXT){
			return TextParser::convert($text);
		}
		
		if($type == KalatContent::TYPE_TEXT){
			return HTMLParser::convert($text);
		}
	}
	
	
}