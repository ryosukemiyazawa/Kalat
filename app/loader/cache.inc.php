<?php
/*
 * cache.inc.php
 * キャッシュがあれば表示して終了する
 */
if(!defined("KALAT_CACHE_ENABLE") || !KALAT_CACHE_ENABLE)return;
//キャッシュの有無を確認
//echo file_get_contents(_SYSTEM_DIR_ . "tmp/_page/index.html");
return;


$usage = memory_get_peak_usage(true);
$usage = ($usage / (1024.0 * 1024.0)) . "MB";

$usage2 = memory_get_usage(true);
$usage2 = ($usage2 / (1024.0 * 1024.0)) . "MB";

//echo "<!-- ";
//echo date("Y-m-d H:i:s", $last_modified);
echo '<center><small>';
echo " " . date("Y-m-d H:i:s");
echo " " . $usage . " " . $usage2;
echo " " . (microtime(true) - $starttime) . " sec";
echo '</small></center>';
//echo " -->";

exit;
