<?php
/* internal */
function run_interactive_client(){

	$client = new KalatClientInteractive();
	$client->run();

}

function run_client($cmd, $args){
	$client = new KalatClient();

	if($client->checkCommand($cmd)){
		$client->execCommand($cmd, $args);
	}else{
		$client->out("unknown command:" . $cmd);
		//@TODO show usage
	}

}

class KalatClient{

	private $contentDir;

	private $cmdMapping = array(
		"sync" => "sync",
		"commit" => "commit",
		"gen" => "build",
		"build" => "build",
		"cleanup" => "cleanup",
		"hello" => "hello"
	);

	function __construct(){
		$this->contentDir = __DIR__ . "/content/";

		if(!file_exists($this->contentDir)){
			mkdir($this->contentDir);
		}
	}

	function checkCommand($cmd){
		if(isset($this->cmdMapping[$cmd])){
			return true;
		}

		return false;
	}
	
	function createToken(){
		$time = time();
		$algo = "sha256";
		$arr = array(
			"slug" => USER_NAME,
			"token" => $algo . "." . hash($algo, AUTH_KEY . "|" . $time),
			"time" => $time
		);
		$token = base64_encode(json_encode($arr));
		return $token;
	}

	function execCommand($cmd, $args = array()){
		if(!isset($this->cmdMapping[$cmd])){
			return;
		}
		$methodName = $this->cmdMapping[$cmd];
		call_user_func_array(array($this,$methodName), $args);
	}
	
	function hello(){
		$this->out("conenct to server");
		$res = $this->call("hello");
		
		if($res["success"]){
			$this->out("hello Mr/Mrs." . $res["name"]);
		}
		
		return $this;
	}

	/**
	 * サーバーと同期する
	 */
	function sync(){
		
		//サーバーにリクエスト
		$this->out("connect to server");

		$res = $this->call("files");
		$files_res = $res;
		if(!$res["success"]){
			$this->out("error!");
			return;
		}

		$download_file_list = array();
		foreach($res["files"] as $array){
				
			$path = $array["path"];
				
			$localpath = CONTENT_DIR . $path;
			$dir = dirname($localpath);
				
			if(file_exists($localpath) && filesize($localpath) > 0){
				if(filemtime($localpath) < $array["utime"]){
					$download_file_list[] = $array;
				}
			}else{
				$download_file_list[] = $array;
			}
		}

		if(count($download_file_list) > 0){
			$this->out("download " . count($download_file_list) . " files.");
	
			foreach($download_file_list as $array){
				$path = $array["path"];
				$res = $this->request(SERVER_URL . "get_content?path=" . $path);
					
				if(strlen($res) > 0){
					$localpath = CONTENT_DIR . $path;
	
					$dir = dirname($localpath);
					if(!file_exists($dir)){
						mkdir($dir, 0700, true);
					}
	
					file_put_contents($localpath, $res);
					touch($localpath, $array["utime"], $array["ctime"]);
				}
			}
			$this->out("download finish!");
		}
		
		
		$res = $this->getStatus($files_res["files"]);
		if(count($res["new"]) > 0){
			$this->out("new file:");
			foreach($res["new"] as $file){
				$this->out("  " . $file);
			}
			$this->out("");
		}
		
		if(count($res["update"]) > 0){
			$this->out("update file:");
			foreach($res["update"] as $file){
				$this->out("  " . $file);
			}
			$this->out("");
		}
		
		$this->out("...");
		$this->out("finish");
		
		return $this;
	}
	
	/*
	 * 掃除
	 */
	function cleanup(){
		
		$isForce = false;
		foreach(func_get_args() as $arg){
			if($arg == "--force"){
				$isForce = true;
			}
		}
		
		$res = $this->call("files");
		if(!$res["success"]){
			$this->out("error!");
			return;
		}
		
		$res = $this->getStatus($res["files"]);
		
		if(count($res["new"]) > 0){
			
			if(!$isForce){
				if(!$this->waitYesNo("are you sure delete " . count($res["new"]) . " files?")){
					return;
				}
			}
		
			foreach($res["new"] as $file){
				$this->out("delete:" . $file);
				$localpath = CONTENT_DIR . $file;
				unlink($localpath);
			}
			$this->out("");
		}
		
	}

	/*
	 * 手元の編集をアップロードする
	*/
	function commit(){

		//サーバーにリクエスト
		$this->out("connect to server");

		$res = $this->call("files");
		if(!$res["success"]){
			$this->out("error!");
			return;
		}
		
		$status = $this->getStatus($res["files"]);

		foreach($status["new"] as $path){
			$localpath = CONTENT_DIR . $path;
				
			if(filesize($localpath) < 1){
				continue;
			}
				
			$this->out("add " . $path . " (" . filesize($localpath) . " bytes)");
			$res = $this->post("upload", array(
				"path" => $path,
				"time" => filemtime($localpath),
				"content" => base64_encode(file_get_contents($localpath))
			));
		}

		foreach($status["update"] as $path){
			$localpath = CONTENT_DIR . $path;
			$this->out("update " . $path);
			$this->post("upload", array(
				"path" => $path,
				"time" => filemtime($localpath),
				"content" => base64_encode(file_get_contents($localpath))
			));
		}

		if(count($status["missing"]) > 0){
			$this->out("missing ");
			foreach($status["missing"] as $path){
				$this->out("\t" . $path);
			}
		}

		$this->out("finish!");

	}

	function build(){

		$this->out("start build");

		$res = $this->call("build");

		if(!$res["success"]){
			$this->out("error!");
			return;
		}

		$this->out("finish!");
	}

	function getStatus($files){

		$local_files = $this->scanDirectory(CONTENT_DIR);

		$new_file_list = array();
		$update_file_list = array();
		$delete_file_list = array();
		$missing_file_list = array();

		$server_file_list = array();
		foreach($files as $array){

			$path = $array["path"];
			$server_file_list[] = $path;
			$localpath = CONTENT_DIR . $path;
				
			if(!isset($local_files[$path])){
				$delete_file_list[] = $path;
				continue;
			}
				
			if(file_exists($localpath) && filesize($localpath) > 0){
				if(filemtime($localpath) > $array["utime"]){
					$update_file_list[] = $path;
				}
			}else{
				$missing_file_list[] = $path;
			}
		}

		foreach($local_files as $path){
				
			if(!in_array($path["path"], $server_file_list)){
				$new_file_list[] = $path["path"];
			}
		}

		return array(
			"new" => $new_file_list,
			"update" => $update_file_list,
			"delete" => $delete_file_list,
			"missing" => $missing_file_list,
		);

	}


	function out($text){
		echo $text;
		echo "\n";
		return $this;
	}

	function call($api, $args = array()){
		$url = SERVER_URL . $api;
		if(count($args) > 0){
			$url .= "?" . http_build_query($args);
		}

		$response = $this->request($url);
		$res = json_decode($response, true);
		if(!$res){
			$this->out("failed to decode request:");
			$this->out($response);
		}
		
		return $res;
	}

	function post($api, $args){
		$url = SERVER_URL . $api;

		$data = http_build_query($args, "", "&");

		$headers = array(
			"Content-Type: application/x-www-form-urlencoded",
			"Content-Length: ".strlen($data)
		);

		$options = array(
			'http'=>array(
				'method'=>'POST',
				'header'=> implode("\r\n", $headers),
				"ignore_errors" => true,
				"content" => $data
			)
		);
		$context = stream_context_create($options);

		$start = microtime(true);
		$res = file_get_contents($url, false, $context);

		return $res;

	}

	function request($url){

		$headers = array(
			"KalatToken: " . $this->createToken()
		);

		$options = array(
			'http'=>array(
				'method'=>'GET',
				'header'=> implode("\r\n", $headers),
				"ignore_errors" => true
			)
		);
		$context = stream_context_create($options);

		$start = microtime(true);
		$res = file_get_contents($url, false, $context);

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
						$fullPath . "/",
						$filePath . "/",
						$childDepth
				);

				$res = array_merge($res, $child_res);
			}else{
				$pathinfo = pathinfo($fullPath);

				$name = $pathinfo["filename"];
				$ext = $pathinfo["extension"];

				$res[$filePath] = array(
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
	
	function waitCommand(){
		while (true) {
			echo "> ";
			$input = fgets(STDIN);
			$input = rtrim($input);
			$array = explode(" ", $input);
			$cmd = array_shift($array);
			$this->exec($cmd, $array);
		}
	}
	
	function waitYesNo($message){
		while (true) {
			echo $message . "[y/n]: ";
			$input = fgets(STDIN);
			$input = rtrim($input);
			
			if($input == "y")return true;
			if($input == "n")return false;
		}
	}

}

class KalatClientInteractive extends KalatClient{

	public function __construct() {
		parent::__construct();
	}


	function run(){
		$this->exec("hello");
	}


	function exec($cmd, $args = array()){
		if(!is_string($cmd)){
			return;
		}
		
		$func = "exec" . ucwords($cmd);
		if(method_exists($this, $func)){
			call_user_func(array($this, $func), $args);
			return;
		}

		$this->out("unknown cmd:" . $cmd)->waitCommand();
	}

	function execHello(){
		$this->hello()->waitCommand();
	}

	function execSync(){
		$this->sync();
		$this->waitCommand();
	}

	function execQuit(){
		$this->out("bye!");
		exit;
	}
	
	function execInfo(){
		//$this->showInfo();
		$this->waitCommand();
	}
	
	function execHelp(){
		$this->out("command list:");
		$this->out("  " . "info");
		$this->out("  " . "sync");
		$this->out("  " . "commit");
		$this->out("  " . "build");
		$this->out("  " . "clear_cache");
		$this->out("  " . "quit");
	}


}