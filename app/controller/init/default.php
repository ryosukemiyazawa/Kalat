<?php
use kalat\SiteConfig;
use kalat\entity\KalatEntry;
use kalat\entity\KalatContent;
use kalat\site\SiteManager;
use kalat\site\SiteHelper;

session_start();
$app->module("session");

$app->bind("post",function($app){ /* @var $app Application */
	
	$site = $_POST["Site"];
	$user = $_POST["User"];
	
	//check value
	$errors = array();
	
	//if(strlen(@$site["name"]) < 1)$errors[] = "site.name";
	//if(strlen(@$site["lang"]) < 1)$errors[] = "site.lang";
	if(strlen(@$site["admin_url"]) < 1)$errors[] = "site.admin_url";
	if(strlen(@$user["name"]) < 1)$errors[] = "user.name";
	if(strlen(@$user["password"]) < 1)$errors[] = "user.password";
	if(strlen(@$user["slug"]) < 1)$errors[] = "user.slug";
	
	if(count($errors) < 1){	//OK
		$app->run("doInit",array(
			"site" => $site,
			"user" => $user
		));
		exit;
	}
	
	$_SESSION["init_site"] = $site;
	$_SESSION["init_user"] = $user;
	
	header("Location:" . INIT_SITE_PATH);
});

$app->bind("get",function($app){ /* @var $app Application */
	
	$site = array(
		"admin_url" => "admin"
	);
	$user = array(
		"name" => "",
		"password" => "",
		"slug" => ""
	);
	if(isset($_SESSION["init_site"])){
		$site = $_SESSION["init_site"];
	}
	if(isset($_SESSION["init_user"])){
		$user = $_SESSION["init_user"];
	}
	
	$page = $app->page("Init Kalat", "start", "_layout/default");
	
	$url = "http://" . $_SERVER["HTTP_HOST"] . INIT_SITE_PATH;
	
	$page->addLabel("site_url",array(
		"text" => $url
	));
	$page->addLabel("site_directory",array(
		"text" => KALAT_DIRECTORY
	));
	
	/* input */
	
	$page->addInput("input_admin_url",array(
		"name" => "Site[admin_url]",
		"value" => @$site["admin_url"]
	));
	$page->addInput("input_admin_name",array(
		"name" => "User[name]",
		"value" => @$user["name"]
	));
	$page->addInput("input_admin_password",array(
		"name" => "User[password]",
		"value" => @$user["password"]
	));
	$page->addInput("input_admin_slug",array(
		"name" => "User[slug]",
		"value" => @$user["slug"]
	));
	
	
	
});

$app->bind("doInit",function($app, $args){
	$site = $args["site"];
	$user = $args["user"];
	
	$root_url = "http://" . $_SERVER["HTTP_HOST"] . INIT_SITE_PATH;
	$admin_url = $site["admin_url"];
	$apiKey = int_makeRandomText(32);
	
	//環境情報を記述
	$envPath = KALAT_DIRECTORY . "conf/env.php";
	$scripts = array();
	$scripts[] = "<?php /* env.php generated at " . date("Y-m-d H:i:s") . " */";
	$scripts[] = 'define("ADMIN_URL", "'.$admin_url.'");';
	$scripts[] = 'define("ADMIN_API_URL", "'.$admin_url.'/api");';
	$scripts[] = 'define("API_KEY", "'.$apiKey.'");';
	$scripts[] = 'define("_SITE_CONTENT_DIRECTORY_", KALAT_DIRECTORY . "content/");';
	$scripts[] = 'define("_SITE_PUBLIC_DIRECTORY_", KALAT_DIRECTORY . "public/");';
	file_put_contents($envPath, implode("\n", $scripts));
	
	//サイトの情報を記述
	$sitePath = KALAT_DIRECTORY . "conf/site.php";
	$scripts = array();
	$scripts[] = "<?php /* site.php generated at " . date("Y-m-d H:i:s") . " */";
	$scripts[] = 'define("_SITE_PUBLIC_URL_", "'.$root_url.'");';
	$scripts[] = 'define("_SITE_PUBLIC_PATH_", "'.INIT_SITE_PATH.'");';
	file_put_contents($sitePath, implode("\n", $scripts));
	
	//ユーザー情報を記述
	$slug = $user["slug"];
	$userPath = KALAT_DIRECTORY . "conf/user/".$slug.".php";
	$scripts = array();
	if(!file_exists(KALAT_DIRECTORY . "conf/user/")){
		mkdir(KALAT_DIRECTORY . "conf/user/", 0700, true);
	}
	
	//default hash algo is sha256
	$hash_algo = "sha256";
	$passwordHash = $hash_algo . "." . hash($hash_algo, $apiKey. "|" . $user["slug"] . "|". $user["password"]);
	
	$scripts[] = "<?php /* site.php generated at " . date("Y-m-d H:i:s") . " */";
	$scripts[] = 'return array(';
	$scripts[] = "\t" . '"name" => "'.$user["name"].'",';
	$scripts[] = "\t" . '"user_id" => "'.$user["slug"].'",';
	$scripts[] = "\t" . '"password" => "'.$passwordHash.'",';
	$scripts[] = "\t" . '"role" => array("administrator"),';
	$scripts[] = ');';
	file_put_contents($userPath, implode("\n", $scripts));
	
	//@TODO _conf/site.phpの生成
	/*
	 * サイト名とか初期レシピの生成とか
	 */
	
	//redirect_url
	$admin_url = $root_url . $admin_url . "/";
	
	//login
	//$app->session->login($user["slug"]);
	
	//clear session
	unset($_SESSION["init_site"]);
	unset($_SESSION["init_user"]);
	
	header("Location: " . $admin_url);
	
	exit;
});

/* internal methods */
function int_makeRandomText($length = 8) {
	static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
	$str = '';
	for ($i = 0; $i < $length; ++$i) {
		$str .= $chars[mt_rand(0, 61)];
	}
	return $str;
}
