<?php
/**
 * @package Fabric
 */
namespace Fabric;

/**
 * Request
 *
 * Responsible for storing details about the current HTTP request and querying
 * it.
 */
class Request {

  /**
   * server
   *
   * @var array
   * @access public
   */
  public $server = array();

  /**
   * get
   *
   * @var array
   * @access public
   */
  public $get = array();

  /**
   * post
   *
   * @var array
   * @access public
   */
  public $post = array();

  /**
   * cookie
   *
   * @var array
   * @access public
   */
  public $cookie = array();

  /**
   * controller
   *
   * @var string
   * @access public
   */
  public $controller;

  /**
   * action
   *
   * @var string
   * @access public
   */
  public $action;

  /**
   * params
   *
   * @var array
   * @access public
   */
  public $params;

  /**
   * ext
   *
   * @var string
   * @access public
   */
  public $ext;

  /**
   * Stores the arguments in the class properties
   *
   * @param array $server
   * @param array $get
   * @param array $post
   * @param array $cookie
   * @access public
   * @return void
   */
  public function __construct(array $server, array $get, array $post, array $cookie) {
    $this->server = $server;
    $this->get = $get;
    $this->post = $post;
    $this->cookie = $cookie;
  }

  /**
   * Returns the key to be used in cache engine to identify the cached content
   * for a given request.
   *
   * @access public
   * @return string
   */
  public function cacheKey() {
    $tmp = $this->server['REQUEST_METHOD'] . $this->server['REQUEST_URI'];
    if ($this->isAjax()) {
      $tmp .= 'XMLHttpRequest';
    }
    return hash('md5', $tmp);
  }

  /**
   * isAjax function
   *
   * @return bool
   **/
  public function isAjax() {
    return isset($this->server['HTTP_X_REQUESTED_WITH']) && $this->server['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' || isset($this->post['isAjax']) && $this->post['isAjax'] == true;
  }

  /**
   * isPjax function
   *
   * @return bool
   **/
  public function isPjax() {
    return $this->isAjax() && isset($this->server['HTTP_X_PJAX']);
  }

}
