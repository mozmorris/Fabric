<?php
/**
 * @package Fabric
 */

namespace Fabric;

/**
 * I18n class
 *
 * @package default
 */
class I18n {

  /**
   * Dependency Injection Container object
   *
   * @var Pimple
   * @access private
   */
  private $__Container;

  /**
   * territory
   *
   * @var string
   * @access public
   */
  public $territory = 'en_GB';

  /**
   * Stores the arguments in the class properties
   *
   * @param string $territory
   * @param Pimple $Container
   * @access public
   * @return void
   */
  public function __construct(\Pimple $Container) {
    $this->__Container = $Container;
  }

  /**
   * date function
   *
   * @param mixed $time - unix timestamp or datetime
   * @return string
   **/
  public function dateFormat($date = null) {
    if (is_null($date)) {
      $date = time();
    }
    $fmt = \IntlDateFormatter::create($this->territory, \IntlDateFormatter::FULL, \IntlDateFormatter::NONE, date_default_timezone_get(), \IntlDateFormatter::GREGORIAN);
    return datefmt_format($fmt, (is_int($date) ? $date : strtotime($date)));
  }

  /**
   * _etTerritory function
   *
   * @param territory
   * @return void
   **/
  public function setTerritory($territory) {
    $this->territory = $territory;

    /**
     * Set language to $territory
     */
    putenv('LANG='.$this->territory);
    putenv('LANGUAGE='.$this->territory);
    setlocale(LC_ALL, $this->territory);

    /**
     * Specify location of translation tables
     */
    bindtextdomain('default', APP_DIR . DIRECTORY_SEPARATOR . 'Languages' . DIRECTORY_SEPARATOR);
    bind_textdomain_codeset('default', 'UTF-8');

    /**
     * Specify domain
     */
    textdomain('default');

  }

}
