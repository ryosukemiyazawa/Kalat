<?php
use kalat\SiteConfig;
use kalat\entity\KalatEntry;
use kalat\entity\KalatContent;
use kalat\site\SiteManager;
use kalat\site\SiteHelper;

$app->module("session")->logout();
header("Location: ". ADMIN_PATH);
