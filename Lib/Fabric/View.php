<?php
/**
 * @package Fabric
 */

namespace Fabric;

/**
 * View
 *
 */
class View {

  /**
   * Dependency Injection Container object
   *
   * @var \Pimple
   * @access private
   */
  private $__Container;

  /**
   * Request object
   *
   * @var \Fabric\Request
   * @access protected
   */
  protected $_Request;

  /**
   * I18n object
   *
   * @var \Fabric\I18n
   * @access protected
   */
  protected $_I18n;

  /**
   * Helper object
   *
   * @var \Fabric\View\Helper
   * @access protected
   */
  protected $_Helper;

  /**
   * Config object
   *
   * @var \Fabric\Config
   * @access protected
   */
  protected $_Config;


  /**
   * Stores the arguments in object properties
   *
   * @param \Pimple $Container
   * @param \Fabric\Request $Request
   * @access public
   * @return void
   */
  public function __construct(\Pimple $Container = null, Request $Request = null) {

    $this->__Container = $Container;
    $this->_Request = $Request;

    /*
      TODO Not sure how this fits in with DCI - well, it doesn't :)
    */
    $this->_Helper = new \Fabric\View\Helper($this, $this->__Container['Config']);

  }

  /**
   * Renders the given data into the given view template and layout and returns
   * the generated content.
   *
   * @param array $data Array where values will be available in variables called
   * their corresponding key names.
   * @param string $template String containing path to the the view template
   * @param mixed $layout String containing path to the layout template, or
   * false for no layout
   * @access public
   * @return string
   */
  public function render(array $data = null, $template, $layout) {

    ob_start();

    if (is_array($data)) {
      extract($data);
    }
    include APP_DIR . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $template;
    $contentForLayout = ob_get_clean();
    if (!$layout) {
      return $contentForLayout;
    }
    ob_start();
    include APP_DIR . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $layout;
    $content = ob_get_clean();
    return $content;

  }

  /**
   * Returns Request object
   *
   * @return \Fabric\Request
   */
  protected function _getRequest() {
    if ($this->_Request) {
      return $this->_Request;
    }
    return $this->_Request = $this->__Container['Request'];
  }

  /**
   * Returns I18n object
   *
   * @return \Fabric\I18n
   */
  protected function _getI18n() {
    if ($this->_I18n) {
      return $this->_I18n;
    }

    return $this->_I18n = $this->__Container['I18n'];
  }

  /**
   * Returns Request object
   *
   * @return \Fabric\View\Helper
   */
  protected function _getHelper() {
    if ($this->_Helper) {
      return $this->_Helper;
    }
  }

  /**
   * Returns the \Fabric\Config object
   *
   * If the object is already available as a property of the controller, that is
   * returned, else it is loaded lazily from the Dependency Injection Container.
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


}
