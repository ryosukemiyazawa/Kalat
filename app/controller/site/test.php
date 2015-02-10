<?php
use kalat\SiteConfig;
use kalat\entity\KalatEntry;
use kalat\entity\KalatContent;
use kalat\site\SiteManager;
use kalat\site\SiteHelper;
use kalat\builder\parser\MarkdownParser;


SiteConfig::loadConfig("site");

$builder = new kalat\builder\SiteBuilder(SiteConfig::getInstance());

SiteHelper::instance();

//サイト情報を構築する
$contentDirectory = SiteConfig::get("content_directory");
$publicDirectory = SiteConfig::get("public_directory");

echo MarkdownParser::convert(file_get_contents($contentDirectory . "_page/markdown.md"));

?>
<style type="text/css">
pre{
	padding:8px;
	background-color:#eee;
}
</style>
