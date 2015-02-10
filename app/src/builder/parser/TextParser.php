<?php

namespace kalat\builder\parser;

use kalat\builder\SiteBuilder;

class TextParser{
	
	public static function convert($text){
		return nl2br($text);
	}
	
}