<?php
define("APP_ROOT", realpath(__DIR__.'/..'));
define("APP_ENV", getenv('APP_ENV') ? getenv('APP_ENV') : 'product');
define("DATA_PATH", APP_ROOT.'/data');

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APP_ROOT . '/vendor'),
    get_include_path(),
)));

$app = new Yaf_Application(APP_ROOT . "/conf/application.ini");
$app->bootstrap()->run();
