<?php
/**
 * @package Fabric
 */

namespace Fabric\View;

/**
 * Helper
 *
 * @uses View
 */
class Helper extends \Fabric\View {

  /**
   * View object
   *
   * @var \Fabric\View
   * @access protected
   */
  protected $_View;

  /**
   * List of available helpers
   *
   * @var array
   * @access protected
   */
  protected $_Helpers = array(
    'Asset'
  );

  /**
   * Config object
   *
   * @var \Fabric\Config
   * @access protected
   */
  protected $_Config;


  public function __construct(\Fabric\View $View = null, \Fabric\Config $Config = null) {

    $this->_View = $View;
    $this->_Config = $Config;

  }

  public function __get($property) {
    if (!empty($this->{$property})) {
      return $this->{$property};
    }

    if (in_array($property, $this->_Helpers) && empty($this->{$property})) {
      $class = '\\Fabric\\View\\Helper\\' . $property . 'Helper';
      $this->{$property} = new $class($this, $this->_Config);
    }

    return $this->{$property};
  }

}
