<?php
/**
 *
 */

namespace Fabric;

/**
 * Model
 *
 */
class Model {

  /**
   * Dependency Injection Container object
   *
   * @var Pimple
   * @access private
   */
  private $__Container;

  /**
   * Cache object
   *
   * @var \Fabric\Cache
   * @access protected
   */
  protected $_Cache;

  /**
   * Config object
   *
   * @var \Fabric\Config
   * @access protected
   */
  protected $_Config;

  /**
   * Database Handle object
   *
   * @var \PDO
   * @access protected
   */
  protected $_DBH;

  /**
   * Validator object
   *
   * @var \Fabric\Validator
   * @access protected
   */
  protected $_Validator;

  /**
   * Database Handle identifier
   *
   * If your application needs to connect to 2 different databases, you can
   * specify which Database Handle identifier here.
   *
   * @var string
   * @access protected
   */
  protected $_conn = 'default';

  /**
   * validationMessages
   *
   * contains messages when a field fails validation
   *
   * @var array
   * @access public
   */
  public $validationMessages = false;

  /**
   * Called when the model is instantiated. Usually, just the Dependency
   * Injection Container object is passed in, but all dependencies can be passed
   * in to facilitate Unit Testing.
   *
   * @param \Pimple $Container
   * @param \Fabric\Cache $Cache
   * @param \Fabric\Config $Config
   * @param \PDO $DBH
   * @param \Fabric\Validator $Validator
   * @access public
   * @return void
   */
  public function __construct($Container = null, $Cache = null, $Config = null, $DBH = null, $Validator = null) {
    $this->__Container = $Container;
    $this->_Cache = $Cache;
    $this->_Config = $Config;
    $this->_DBH = $DBH;
    $this->_Validator = $Validator;
  }

  /**
   * Returns the \Fabric\Cache object
   *
   * If the object is already available as a property of the model, that is
   * returned, else it is loaded lazily from the Dependency Injection Container.
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
   * Returns the \Fabric\Config object
   *
   * If the object is already available as a property of the model, that is
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

  /**
   * Returns the Database Handle object (instance of PDO)
   *
   * If the object is already available as a property of the model, that is
   * returned, else it is loaded lazily from the Dependency Injection Container.
   *
   * The actual object loaded is the one identified by the key DBH.conn, where
   * conn is the value in the models $_conn property.
   *
   * @access protected
   * @return \PDO
   */
  protected function _getDBH() {
    if ($this->_DBH) {
      return $this->_DBH;
    }
    return $this->_DBH = $this->__Container['DBH.' . $this->_conn];
  }

  /**
   * Returns the \Fabric\Validator object
   *
   * If the object is already available as a property of the model, that is
   * returned, else it is loaded lazily from the Dependency Injection Container.
   *
   * @access protected
   * @return \Fabric\Validator
   */
  protected function _getValidator() {
    if ($this->_Validator) {
      return $this->_Validator;
    }
    return $this->_Validator = $this->__Container['Validator'];
  }

  /**
   * Validate function
   *
   * @param array $data
   */
  public function validate($data) {

    if (empty($data) || !is_array($data)) {
      return false;
    }

    foreach ($data as $name => $value) {

      /**
       * If a validation rule for the current value has been
       * defined in the model, we call the relevant validation function
       */
      if (array_key_exists($name, $this->_validate)) {

        foreach ($this->_validate[$name] as $type => $criteria) {

          /**
           * If the Validator class has a corresponding method for the validation type,
           * this method is passed the value and the rule. If the method returns false
           * we populate $this->validationMessages with the corresponding validation message
           */
          if (method_exists($this->_getValidator(), $type)) {

            if (!empty($value)) {

              if (!$this->_getValidator()->{$type}($value, $this->_validate[$name][$type])) {

                // validation failed, show message is defined
                if (!empty($this->_validate[$name][$type]['message'])) {

                  $this->validationMessages[$name] = _($this->_validate[$name][$type]['message']);

                // else show default message
                } else {

                  $this->validationMessages[$name] = sprintf(_('%s not valid.'), ucfirst(str_replace(array('-', '_'), ' ', $name)));

                }
              }

            // check to see if this value can be left empty
            } elseif (isset($this->_validate[$name]['allowEmpty'])) {

              // if false, use default message with input name
              if ($this->_validate[$name]['allowEmpty'] === false) {

                $this->validationMessages[$name] = sprintf(_('%s cannot be left empty.'), ucfirst(str_replace(array('-', '_'), ' ', $name)));

              // else attempt to use a custom message
              } elseif (is_array($this->_validate[$name]['allowEmpty'])) {

                if (!empty($this->_validate[$name]['allowEmpty']['message'])) {

                  $this->validationMessages[$name] = $this->_validate[$name]['allowEmpty']['message'];

                } else {

                  $this->validationMessages[$name] = sprintf(_('%s cannot be left empty.'), ucfirst(str_replace(array('-', '_'), ' ', $name)));

                }
              }
            }
          }
        }
      }
    }

    if (!empty($this->validationMessages)) {
      return false;
    }

    return true;

  }

}
