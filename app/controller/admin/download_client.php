<?php
use kalat\SiteConfig;
use kalat\entity\KalatEntry;
use kalat\entity\KalatContent;
use kalat\site\SiteManager;
use kalat\site\SiteHelper;

$app->module("session")->checkSession();


$app->bind("get",function($app){ /* @var $app Application */
	
	//header("Content-Type: text/html; ");
	
	$url = _SITE_PUBLIC_URL_ . ADMIN_API_URL . "/";
	
	$user = $app->session->getConfig("user_id");
	$password = $app->session->getConfig("password");
	$key = md5(API_KEY . "|" . $password);
	
	$scripts = array();
	$scripts[] = '<?php ';
	$scripts[] = 'define("SERVER_URL","'.$url.'");';
	$scripts[] = 'define("USER_NAME","'.$user.'");';
	$scripts[] = 'define("AUTH_KEY", "'.$key.'");';
	$scripts[] = 'define("CONTENT_DIR", __DIR__ . "/content/");';
	$scripts[] = 'if($argc <= 1){run_interactive_client();}';
	$scripts[] = '$args = array_splice($argv, 1);';
	$scripts[] = '$cmd = array_shift($args);';
	$scripts[] = 'run_client($cmd, $args);';
	$scripts[] = 'exit;';
	$scripts[] = '';
	
	
	$client_script = file_get_contents(_SYSTEM_DIR_ . "src/data/kalat_client.php");
	//replace comment
	$client_script = str_replace("<?php","", $client_script);
	$client_script = preg_replace("#//.+#", "", $client_script);
	$client_script = preg_replace("#/\*.*?\*/#s", "", $client_script);
	$client_script = preg_replace("#[\r\n\t]#", "", $client_script);
	
	$scripts[] = $client_script;
	
	
	$scripts = implode("\n", $scripts);
	
	header('Content-Disposition: attachment; filename="kalat_client.php"');
	header('Content-Type: application/octet-stream');
	header('Content-Transfer-Encoding: binary');
	header('Content-Length: '.strlen($scripts));
	
	echo $scripts;
	
	exit;
	
});

