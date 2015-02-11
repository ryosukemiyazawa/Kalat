<?php
use kalat\site\SiteHelper;
use kalat\site\SiteManager;
use kalat\SiteConfig;
/*
	パンくずリストを作る
*/
//パンくずの設定を読み込む
SiteConfig::loadConfig("breadcrumb");

$dir = SiteHelper::get("current_directory");
$currentPage = SiteHelper::get("current_page");
$tree = array();

if($currentPage > 0){
	$tree[] = ($currentPage+1) . "ページ";
}else{
	$tree[] = SiteHelper::get("current_label");
}
if(SiteHelper::get("page_type") != "entry"){
	$dir = dirname($dir);
}

$manager = SiteManager::getInstance();
while(true){
	$pageConfig = $manager->get($dir);
	$title = SiteConfig::get("breadcrumb." . $dir, $pageConfig["title"]);
	if(!$title)$title = basename($dir);
	$tree[] = [substr($dir,1), $title];
	
	if($dir == "/"){
		break;
	}
	
	$dir = dirname($dir);
	if($dir != "/")$dir .= "/";
}
$tree = array_reverse($tree);
?>
<ul class="breadcrumb">
	<?php foreach($tree as $array){ ?>
		<?php if(is_array($array)){ ?>
			<li><a href="<?php printh(_SITE_PUBLIC_PATH_ . $array[0]); ?>"><?php printh($array[1]); ?></a></li>
		<?php }else{ ?>
			<li class="active"><?php printh($array); ?></li>
		<?php } ?>
	<?php } ?>
</ul>