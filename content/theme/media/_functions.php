<?php
use kalat\site\SiteHelper;
/*
 * このファイルは自動的に読み込まれる
 */
if(!function_exists("_navbar_print_tree")){
	
	function _navbar_print_tree($page){
		$current_dir = SiteHelper::get("current_directory");
		$page_slug = $page[0];
		$page_url = ($page_slug == "_home") ? "/" : "/" . $page_slug . "/";
		
		$link = _SITE_PUBLIC_PATH_ . $page_slug;
		if($page_slug == "_home")$link = _SITE_PUBLIC_PATH_;
		$active = ($current_dir == $page_url) ? ' class="active"' : "";
		$caption = $page[1];
		$caption = $page[1];
		
		if(count($page) == 2){
			echo '<li'.$active.'><a href="'.$link.'">'.$caption.'</a></li>';
		}else{
			echo '<li class="dropdown">';
			echo '<a class="dropdown-toggle" data-toggle="dropdown">'.$caption.' <span class="caret"></span></a>';
			echo '<ul class="dropdown-menu" role="menu">';
				foreach($page[2] as $_page){
					_navbar_print_tree($_page);
				}
			echo '</ul>';
			echo '</li>';
		}
		
		echo "\n";
		
	}
	
}