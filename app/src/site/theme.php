<?php

use kalat\builder\SiteBuilder;
kalat_add_shortcode("image",function($text, $args = array()){
	return SiteBuilder::getCurrentBuilder()->doShortCodeImage($text, $args);
});

kalat_add_shortcode("finish",function($text, $agrs = array()){
	return "【済】";
});

kalat_add_shortcode("index",function($text, $args = array(), $parser){
	$wrap = (isset($args["wrap"])) ? $args["wrap"] : "ol";
	$class = (isset($args["class"])) ? $args["class"] : "topic-nav";
	return '<'.$wrap.' class="'.$class.'">' .$parser->doShortCodeIndex($text, $args) . '</'.$wrap.'>';
});