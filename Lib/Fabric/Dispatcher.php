<?php
/**
 *
 */

namespace Fabric;

/**
 * Dispatcher
 *
 */
class Dispatcher {

  /**
   * The Dependency Injection Container Object
   *
   * @var \Pimple
   * @access private
   */
  private $__Container;

  /**
   * Config object
   *
   * @var \Fabric\Config
   * @access protected
   */
  protected $_Config;

  /**
   * Cache object
   *
   * @var \Fabric\Cache
   * @access protected
   */
  protected $_Cache;

  /**
   * Router object
   *
   * @var \Fabric\Router
   * @access protected
   */
  protected $_Router;

  /**
   * Request object
   *
   * @var \Fabric\Request
   * @access protected
   */
  protected $_Request;

  /**
   * Controller object
   *
   * @var \Fabric\Controller
   * @access protected
   */
  protected $_Controller;

  /**
   * ErrorController object
   *
   * @var \Fabric\Controller
   * @access protected
   */
  protected $_ErrorController;

  /**
   * Controller name
   *
   * @var string
   * @access protected
   */
  protected $_controllerName;

  /**
   * The action to be dispatched on the Controller object
   *
   * @var string
   * @access protected
   */
  protected $_action;

  /**
   * View object
   *
   * @var \Fabric\View
   * @access protected
   */
  protected $_View;

  /**
   * Response object
   *
   * @var mixed
   * @access protected
   */
  protected $_Response;

  /**
   * Called when the dispatcher is instantiated which is usually just the once
   * when the Request has been parsed by the Router and the controller and
   * action have been identified. Usually, just the Dependency Injection
   * Container object is passed in, but all dependencies can be passed in to
   * facilitate Unit Testing.
   *
   * @param \Pimple $Container
   * @param \Fabric\Config $Config
   * @param \Fabric\Cache $Cache
   * @param \Fabric\Router $Router
   * @param \Fabric\Request $Request
   * @param \Fabric\Controller $Controller
   * @param \Fabric\ErrorController $ErrorController
   * @param string $action
   * @param \Fabric\View $View
   * @param \Fabric\Response $Response
   * @access public
   * @return void
   */
  public function __construct(\Pimple $Container = null, Config $Config = null, Cache $Cache = null, Router $Router = null, Request $Request = null, Controller $Controller = null, Controller $ErrorController = null, $action = null, View $View = null, Response $Response = null, I18n $I18n = null) {
    $this->__Container = $Container;
    $this->_Config = $Config;
    $this->_Cache = $Cache;
    $this->_Router = $Router;
    $this->_Request = $Request;
    $this->_Controller = $Controller;
    $this->_ErrorController = $ErrorController;
    $this->_action = $action;
    $this->_View = $View;
    $this->_Response = $Response;
    $this->_I18n = $I18n;
  }

  /**
   * dispatch
   *
   * @access public
   * @return
   */
  public function dispatch() {
    if (($cachedResponse = $this->_getCachedResponse()) != false) {
      return $cachedResponse;
    }
    try {
      $this->_getRouter()->parse($this->_getRequest());
      $this->_getI18n();
      $Controller = $this->_getController();
      $action = $this->_getAction();
      $Response = $this->_invoke($Controller, $action);
    } catch (\Exception $e) {
      $mode = $this->_getConfig()->environment['mode'];
      if ($mode != 'prod') {
        throw $e;
      }
      $Response = $this->_handleError($e);
    }
    $this->_cacheResponse($Response);
    return $Response;
  }

  /**
   * Attempts to fetch the content for the given request from the cache
   *
   * @access protected
   * @return \Fabric\Response or false on failure
   */
  protected function _getCachedResponse() {
    $Config = $this->_getConfig();
    $cacheRequests = $Config->environment['cache']['cache_requests'];
    if (!$cacheRequests) {
      return false;
    }
    $Request = $this->_getRequest();
    $requestMethod = $Request->server['REQUEST_METHOD'];
    if ($requestMethod != 'GET') {
      return false;
    }
    if (!empty($_GET['preview'])) {
      return false;
    }
    $requestCacheKey = $Request->cacheKey();
    $Cache = $this->_getCache();
    if (($cache = $Cache->read($requestCacheKey)) === false) {
      return false;
    }
    $Response = $this->_getResponse();
    $Response->body = $cache;
    return $Response;
  }

  /**
   * Returns an instance of \Fabric\Response with the body property populated
   *
   * Calls the method specified in $action on the $Controller object to get
   * any data required by the View. Determines the template and layout and
   * renders the view, and stores the content in the body property of the
   * Response object.
   *
   * @access protected
   * @return \Fabric\Response
   */
  protected function _invoke($Controller, $action) {

    $data = $Controller->$action($this->_getRequest()->params);
    $View = $this->_getView();
    $template = $Controller->getTemplate($action);
    $layout = $Controller->getLayout();
    $Response = $this->_getResponse();
    $Response->body = $View->render($data, $template, $layout);
    return $Response;
  }

  /**
   * Handles exceptions thrown, usually in non production mode.
   *
   * @param \Exception $e
   * @access protected
   * @return \Fabric\Response
   */
  protected function _handleError(\Exception $e) {
    $ErrorController = $this->_getErrorController();
    $errorCode = $e->getCode();
    if (!$errorCode) {
      $errorCode = 404;
    }
    $errorAction = 'error' . $errorCode;
    return $this->_invoke($ErrorController, $errorAction);
  }

  /**
   * Attempts to cache the Response body
   *
   * @access protected
   * @return boolean
   */
  protected function _cacheResponse(Response $Response) {
    $Config = $this->_getConfig();
    $cacheRequests = $Config->environment['cache']['cache_requests'];
    if (!$cacheRequests) {
      return false;
    }
    $Request = $this->_getRequest();
    $requestMethod = $Request->server['REQUEST_METHOD'];
    if ($requestMethod != 'GET') {
      return false;
    }
    $requestCacheKey = $Request->cacheKey();
    $Cache = $this->_getCache();
    return $Cache->write($requestCacheKey, $Response->body);
  }

  /**
   * Returns an instance of \Fabric\Config for getting config data
   *
   * If this object doesn't have the Config object, it asks the Container
   * for it.
   *
   * @access protected
   * @return \Fabric\Config
   */
  protected function _getConfig() {
    if ($this->_Config) {
      return $this->_Config;
    }
    return $this->_Config = $this->__Container['Config'];
  }

  /**
   * Returns an instance of \Fabric\Cache for dealing with the Cache Engine
   *
   * If this object doesn't have the Cache object, it asks the Container
   * for it.
   *
   * @access protected
   * @return \Fabric\Cache
   */
  protected function _getCache() {
    if ($this->_Cache) {
      return $this->_Cache;
    }
    return $this->_Cache = $this->__Container['Cache'];
  }

  /**
   * Returns an instance of \Fabric\Controller for handling errors
   *
   * If this object doesn't have the Error Controller, it asks the Container
   * for it.
   *
   * @access protected
   * @return \Fabric\Controller
   */
  protected function _getErrorController() {
    if ($this->_ErrorController) {
      return $this->_ErrorController;
    }
    return $this->_ErrorController = $this->__Container['Controller']($this->__Container, 'App\Controller\Error');
  }

  /**
   * Returns an instance of \Fabric\Request
   *
   * @access protected
   * @return \Fabric\Request
   */
  protected function _getRequest() {
    if ($this->_Request) {
      return $this->_Request;
    }
    return $this->_Request = $this->__Container['Request'];
  }

  /**
   * Returns an instance of \Fabric\Router
   *
   * @access protected
   * @return \Fabric\Router
   */
  protected function _getRouter() {
    if ($this->_Router) {
      return $this->_Router;
    }
    return $this->_Router = $this->__Container['Router'];
  }

  /**
   * Returns an instance of \Fabric\Controller
   *
   * If this object doesn't have a Controller, it gets the controller name from
   * the Request object and asks the Container for it.
   *
   * @access protected
   * @return \Fabric\Controller
   */
  protected function _getController() {
    if ($this->_Controller) {
      return $this->_Controller;
    }
    $controllerName = $this->_getControllerName();
    return $this->_Controller = $this->__Container['Controller']($this->__Container, $controllerName);
  }

  /**
   * Returns the name of the controller to which the action to be dispatched
   * belongs.
   *
   * @access protected
   * @return string
   */
  protected function _getControllerName() {
    if ($this->_controllerName) {
      return $this->_controllerName;
    }
    return $this->_controllerName = $this->__Container['Request']->controller;
  }

  /**
   * Returns the action to be dispatched
   *
   * @access protected
   * @return string
   */
  protected function _getAction() {
    if ($this->_action) {
      return $this->_action;
    }
    return $this->_action = $this->__Container['Request']->action;
  }

  /**
   * Returns an instance of \Fabric\Response
   *
   * @access protected
   * @return \Fabric\View
   */
  protected function _getView() {
    if ($this->_View) {
      return $this->_View;
    }
    return $this->_View = $this->__Container['View'];
  }

  /**
   * Returns and instance of \Fabric\Response
   *
   * @access protected
   * @return \Fabric\Response
   */
  protected function _getResponse() {
    if ($this->_Response) {
      return $this->_Response;
    }
    return $this->_Response = $this->__Container['Response'];
  }

  /**
   * Returns and instance of \Fabric\I18n
   *
   * @access protected
   * @return \Fabric\I18n
   */
  protected function _getI18n() {
    if ($this->_I18n) {
      return $this->_I18n;
    }
    return $this->_I18n = $this->__Container['I18n'];
  }

}
