<?php
use kalat\SiteConfig;
use kalat\entity\KalatEntry;
use kalat\entity\KalatContent;
use kalat\site\SiteManager;
use kalat\site\SiteHelper;
use kalat\builder\SiteBuilder;

$app->module("session")->checkSession();

SiteConfig::loadConfig("site");
SiteConfig::loadConfig("collections");

$app->bind("get",function($app){ /* @var $app Application */
	
	$start = microtime(true);
	
	$builder = new SiteBuilder(SiteConfig::getInstance());
	$builder->setAuthor($app->session->getSlug());
	$builder->build(SiteHelper::instance());
	
	$end = microtime(true);
	
	$page = $app->page("Build", "build", "_layout/default");
	
	$page->addLabel("build_time",array(
		"text" => ($end - $start)
	));
});
