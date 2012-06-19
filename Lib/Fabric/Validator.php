<?php
/**
 * @package Fabric
 */

namespace Fabric;

/**
 * Validator class
 *
 **/
class Validator {

  /**
   * Dependency Injection Container object
   *
   * @var \Pimple
   * @access private
   */
  private $__Container;

  public function __construct(\Pimple $Container) {

    $this->__Container = $Container;

  }

  /**
   * @param string $value
   * @param array $criteria
   * @return bool
   */
  public function allowEmpty($value, $criteria) {
    return $value;
  }

  /**
   * Checks a value against the supplied regex
   *
   * @param string $value
   * @param array $criteria
   * @return bool
   */
  public function regex($value, $criteria) {
    if (empty($criteria['rule'])) {
      return true;
    }
    return preg_match($criteria['rule'], $value);
  }

  /**
   * Validates an email address
   *
   * @param string $value
   * @param array $criteria
   * @return bool
   */
  public function email($value, $criteria) {
    return preg_match('/^(?:(?:(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|\x5c(?=[@,"\[\]\x5c\x00-\x20\x7f-\xff]))(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]\x5c\x00-\x20\x7f-\xff]|\x5c(?=[@,"\[\]\x5c\x00-\x20\x7f-\xff])|\.(?=[^\.])){1,62}(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]\x5c\x00-\x20\x7f-\xff])|[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]{1,2})|"(?:[^"]|(?<=\x5c)"){1,62}")@(?:(?!.{64})(?:[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.?|[a-zA-Z0-9]\.?)+\.(?:xn--[a-zA-Z0-9]+|[a-zA-Z]{2,6})|\[(?:[0-1]?\d?\d|2[0-4]\d|25[0-5])(?:\.(?:[0-1]?\d?\d|2[0-4]\d|25[0-5])){3}\])$/', $value);

  }

  /**
   * Validates a phone number
   *
   * @param string $value
   * @param array $criteria
   * @return bool
   */
  public function phone($value, $criteria) {
    return preg_match('/^[0-9\+ ]*$/i', $value);
  }

}