<?php
namespace kalat\api;



use kalat\SiteConfig;
class Manager{
	
	
	function getFiles($directories){
		
		$contentDirectory = SiteConfig::get("content_directory");

		$res = array();
		foreach($directories as $dir){
			$res = array_merge($res, $this->scanDirectory($contentDirectory . $dir . "/", $dir . "/"));
		}
		return $res;
	}
	
	
	private function scanDirectory($path, $origin = "", $depth = 0){
		
		if(!file_exists($path)){
			return array();
		}
		
		$res = array();
		$files = scandir($path);
		$childDepth = $depth+1;
	
		foreach($files as $file){
			if($file[0] == ".")continue;
			
			$fullPath = $path . $file;
			$filePath = $origin . $file;
			if(is_dir($fullPath)){
				$child_res = $this->scanDirectory(
						$fullPath . DIRECTORY_SEPARATOR,
						$filePath . DIRECTORY_SEPARATOR,
						$childDepth
				);
	
				$res = array_merge($res, $child_res);
			}else{
				$pathinfo = pathinfo($fullPath);
	
				$name = $pathinfo["filename"];
				$ext = $pathinfo["extension"];
	
				$res[] = array(
					"name" => $name,
					"filename" => $file,
					"ext" => $ext,
					"path" => $filePath,
					"full" => $fullPath,
					"ctime" => filectime($fullPath),
					"utime" => filemtime($fullPath),
				);
			}
		}
	
		return $res;
	}
	
}