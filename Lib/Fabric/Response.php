<?php
/**
 * @package Fabric
 */

namespace Fabric;

/**
 * Response
 *
 * @todo Implement sending correct headers according to response type
 */
class Response {

  /**
   * Dependency Injection Container object
   *
   * @var \Pimple
   * @access private
   */
  private $__Container;

  private $__Extensions = array(
    'html' => 'text/html',
    'php'  => 'text/html',
    'rss'  => 'application/rss+xml'
  );

  /**
   * Stores the body of the response
   *
   * @var string
   * @access public
   */
  public $body = '';

  public function __construct(\Pimple $Container = null) {
    $this->__Container = $Container;
    $this->_setHeaders();
  }

  private function _setHeaders() {
    $type = $this->_getContentType($this->__Container['Request']->server['REQUEST_URI']);
    header('Content-Type: ' . $type . '; charset=UTF-8');
  }

  private function _getContentType($uri) {
    // attempt to match a file extension
    if (preg_match('/\.[0-9a-zA-Z]*$/', $uri, $match)) {
      $ext = ltrim($match[0], '.');

      // return content type
      if (!empty($this->__Extensions[$ext])) {
        return $this->__Extensions[$ext];
      }
    }

    return 'text/html';
  }

}
