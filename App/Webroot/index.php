<?php

/**
 * Define main directories and load the SPL autoloader and register the libs
 */
define('WEB_ROOT', dirname(__FILE__));
define('APP_DIR', dirname(WEB_ROOT));

define('ROOT_DIR', dirname(APP_DIR));
define('LIB_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'Lib');
require(LIB_DIR . DIRECTORY_SEPARATOR . 'SplClassLoader.php');

$classLoader = new SplClassLoader('App', ROOT_DIR);
$classLoader->register();

require(LIB_DIR . DIRECTORY_SEPARATOR . 'Fabric' . DIRECTORY_SEPARATOR . 'Bootstrap.php');

define('CONFIG_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'Config');
define('CACHE_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'Tmp' . DIRECTORY_SEPARATOR . 'Cache');
require(CONFIG_DIR . DIRECTORY_SEPARATOR . 'Common.php');
$Container['common'] = $common;
require(CONFIG_DIR . DIRECTORY_SEPARATOR . 'Environment.php');
$Container['environment'] = $environment;

/**
 * Load the routes, Router, add the Request object to the Dependency Injection
 * Container and try and get the Router to parse the Request object and identify
 * the controller and action
 */

require(CONFIG_DIR . DIRECTORY_SEPARATOR . 'Routes.php');
$Container['routes'] = $routes;

$Dispatcher = new Fabric\Dispatcher($Container);
$Response = $Dispatcher->dispatch();

echo $Response->body;