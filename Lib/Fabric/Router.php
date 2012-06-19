<?php
/**
 * Router
 *
 * Responsible for matching a request to a Controller/Action
 *
 * @package Fabric
 */
namespace Fabric;

/**
 * Router
 *
 * Responsible for matching a request to a controller/action
 */
class Router {

  /**
   * Array of routes with keys indicating a route pattern and values an array
   * with keys controller and action. Value of the controller should be a
   * string representing the namespaced controller class name and the value of
   * the action should be a string representing the method name in the
   * controller.
   *
   * @var array
   * @access protected
   */
  protected $_routes = array();

  /**
   * Called on instantiation of a Router object, stores the passed $routes
   * array in the $_routes class property.
   *
   * @param array $routes
   * @access public
   * @return void
   */
  public function __construct(array $routes) {

    $this->_routes = $routes;

  }

  /**
   * Populates the controller and action properties of the passed Request
   * object according to the matched route.
   *
   * Iterates over the $_routes class property, converting the route (stored in
   * the key) to a regular expression pattern that the REQUEST_URI is macthed
   * against. If no match is found, an Exception is thrown.
   *
   * Variable parameters (those prefixed by a colon) in the route are captured
   * and stored in the Request::params property.
   *
   * @param \Fabric\Request $Request
   * @access public
   * @return void
   */
  public function parse(\Fabric\Request $Request) {

    foreach ($this->_routes as $route => $config) {

      $pattern = $this->_regex($route);

      if (strpos($Request->server['REQUEST_URI'], '?')) {
        $subject = substr($Request->server['REQUEST_URI'], 0, strpos($Request->server['REQUEST_URI'], '?'));
      } else {
        $subject = $Request->server['REQUEST_URI'];
      }

      if (preg_match($pattern, $subject, $Request->params)) {
        $Request->controller = $config['controller'];
        $Request->action = $config['action'];

        // default params
        if (!empty($config['params']) && is_array($config['params'])) {
          $Request->params = array_merge($config['params'], $Request->params);
        }

        return;
      }

    }

    throw new \Exception('No matching route', 404);
  }

  /**
   * Returns a regular expression pattern for a given route string that can be
   * used to match the REQUEST_URI to a route.
   *
   * Converts a route such as
   *
   * /:param1/static_param/:param2
   *
   * to
   *
   * /\/(?P<param1>)\/static_param\/(?P<param2>)/
   *
   * Note the pattern contains named capture subpatterns, such that matches for
   * the variable params, i.e. :param1 and :param2, are captured and available
   * in the matches variable with keys param1 and param2.
   *
   * @param string $route
   * @access protected
   * @return string
   */
  protected function _regex($route) {

    $replace = array(
      '/:([^\/]+)/' => '(?P<$1>[^/]+)',
      '/\//' => '\\/',
      '/^(.*)$/' => '/^$1$/'
    );

    $pattern = preg_replace(array_keys($replace), $replace, $route);

    return $pattern;

  }
}
