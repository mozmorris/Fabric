<?php
/**
 * @package Fabric
 */

namespace Fabric;

/**
 * Config
 *
 */
class Config {

  /**
   * Stores configuration data common to all environments
   *
   * @var array
   * @access public
   */
	public $common = array();

  /**
   * Stores configuration data specific to the current environment
   *
   * @var array
   * @access public
   */
  public $environment = array();

  /**
   * Stores passed configuration data in object properties
   *
   * @param mixed $common
   * @param mixed $environment
   * @access public
   * @return void
   */
  public function __construct(array $common = array(), array $environment = array()) {
		$this->common = $common;
		$this->environment = $environment;
	}

}

