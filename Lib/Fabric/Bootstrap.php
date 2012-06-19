<?php

$classLoader = new SplClassLoader('Fabric', LIB_DIR);
$classLoader->register();

/**
 * Load the Dependency Injection Container and fill it with Config and Cache
 * objects (as these are required to see if we've a cache hit)
 */

require_once LIB_DIR . DIRECTORY_SEPARATOR . 'Pimple' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Pimple.php';

$Container = new Pimple();

/**
 * Add the lambda function that returns a new \Fabric\Config object to the DI
 * Container as a shared object, meaning that any subsequent requests for the
 * Config object, by accessing $Container['Config'], will return the same object
 */
$Container['Config'] = $Container->share(function (\Pimple $Container) {
  $common = $environment = null;
  if ($Container->offsetExists('common')) {
    $common = $Container['common'];
  }
  if ($Container->offsetExists('environment')) {
    $environment = $Container['environment'];
  }
  return new Fabric\Config($common, $environment);
});

/**
 * Add the lambda function that returns a new concrete object instance of the
 * \Fabric\Cache class, according to the environment config, to the DI Container as
 * a shared object, meaning that any subsequent requests for the Cache object,
 * by accessing $Container['Cache'], will return the same object.
 */
$Container['Cache'] = $Container->share(function (\Pimple $Container) {
  return new $Container['Config']->environment['cache']['engine']($Container['Config']->environment['cache']);
});

/**
 * Add the lambda that returns a new PDO object with the database configuration
 * details provided, to the DI Container, as a parameter, meaning that it is
 * only evaluated when called (@todo confirm this explanation).
 */
$Container['DBH'] = $Container->protect(function (\Pimple $Container, array $config) {
  $pdo = new PDO($config['dsn'], $config['user'], $config['pass'], $config['options']);
  if (!empty($config['attributes'])) {
    foreach ($config['attributes'] as $attribute => $value) {
      $pdo->setAttribute($attribute, $value);
    }
  }
  return $pdo;
});

/**
 * Add the lambda that returns a new Database Handle object with the default
 * database configuration details, as a shared object, meaning that subsequent
 * requests for the Database Handle object, by accessing
 * $Container['DBH.default'], will return the same object.
 */
$Container['DBH.default'] = $Container->share(function (\Pimple $Container) {
  return $Container['DBH']($Container, $Container['Config']->environment['database']['default']);
});

/**
 * Add the lambda that returns a new Validator object, as a shared object, meaning
 * that subsequent requests for the Validator object, by accessing $Container['Validator']
 * will return the same object.
 */
$Container['Validator'] = $Container->share(function (\Pimple $Container) {
  return new Fabric\Validator($Container);
});

/**
 * Add the lambda that returns a new Model object of the name requested, to the
 * DI Container, as a parameter, meaning that it is only evaluated when called
 * (@todo confirm this explanation).
 *
 * @todo Consider whether it would be good to be able to load models outside
 * the App\Model namespace, in which case, should the $name parameter be
 * namespaced, or should there be logic to cope with either, where no namespace
 * assumes App\Model?
 */
$Container['Model'] = $Container->protect(function (\Pimple $Container, $name) {
  $Model = 'App\Model\\'.$name;
  return new $Model($Container);
});

/**
 * Add the lambda that returns a new Router object as a shared object meaning
 * that subsequent requests for the Router object, by accessing
 * $Container['Router'], will return the same object.
 *
 * In case for some reason you don't specify any routes in your app, the routes
 * array is only passed in if it's available in the Container.
 */
$Container['Router'] = $Container->share(function (\Pimple $Container) {
  $routes = array();
  if ($Container->offsetExists('routes')) {
    $routes = $Container['routes'];
  }
  return new Fabric\Router($routes);
});

/**
 * Add the lambda that returns a new Request object, as a shared object, meaning
 * that subsequent requests for the Request object, by accessing
 * $Container['Request'], will return the same object.
 */
$Container['Request'] = $Container->share(function (\Pimple $Container) {
  return new Fabric\Request($_SERVER, $_GET, $_POST, $_COOKIE);
});

/**
 * Add the lambda that returns a new Controller object of the name requested,
 * to the DI Container, as a parameter, meaning that it is only evaluated when
 * called (@todo confirm this explanation).
 *
 * @todo Consider whether it would be good to be able to load controllers
 * outside the App\Controller namespace, in which case, should the $name
 * parameter be namespaced, or should there be logic to cope with either, where
 * no namespace assumes App\Controller?
 */
$Container['Controller'] = $Container->protect(function (\Pimple $Container, $name) {
  return new $name($Container);
});

/**
 * Add the lambda that returns a new View object, as a shared object, meaning
 * that subsequent requests for the View object, by accessing $Container['View']
 * will return the same object.
 */
$Container['View'] = $Container->share(function (\Pimple $Container) {
  return new Fabric\View($Container);
});

/**
 * Add the lambda that returns a new Response object, as a shared object, meaning
 * that subsequent requests for the Response object, by accessing
 * $Container['Response'] will return the same object.
 */
$Container['Response'] = $Container->share(function (\Pimple $Container) {
  return new Fabric\Response($Container);
});

/**
 * Add the lambda that returns a new I18n object, as a shared object, meaning
 * that subsequent requests for the I18n object, by accessing
 * $Container['I18n'] will return the same object.
 */
$Container['I18n'] = $Container->share(function (\Pimple $Container) {
  return new Fabric\I18n($Container);
});
