<?php
use kalat\SiteConfig;
$app->module("api")->checkToken();

/*
 * hello.php
 * ユーザー名を表示する
 *
 */

$app->bind("get",function($app, $args){ /* @var $app Application */
	$app->api->result(array("name" => SiteConfig::get("user.name")));
});
