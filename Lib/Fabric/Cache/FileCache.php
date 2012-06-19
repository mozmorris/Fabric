<?php
/**
 * @package Fabric
 */

namespace Fabric\Cache;

/**
 * FileCache
 *
 * Concrete implementation of \Fabric\Cache
 *
 * @uses Cache
 */
class FileCache extends \Fabric\Cache {

  /**
   * @inheritdoc
   */
  public function write($key, $data, $config = 'default', $options = array()) {
    $this->_time = time();
    $options = array_merge($this->_config[$config], $options);
    $dataToWrite = $this->_createData($data, $options);
    file_put_contents($this->_getCacheKey($key), $dataToWrite);
  }

  /**
   * @inheritdoc
   */
  public function read($key) {
    $this->_time = time();
    $cacheKey = $this->_getCacheKey($key);
    if (!file_exists($this->_getCacheKey($key))) {
      return false;
    }
    try {
      $data = file_get_contents($cacheKey);
    } catch (Exception $e) {
      return false;
    }
    list($expires, $earlyExpires, $data) = $this->_parseData($data);
    if ($this->_expired($expires)) {
      return false;
    }
    if ($earlyExpires && $this->_earlyExpires($expires, $earlyExpires)) {
      return false;
    }
    return $data;
  }

  /**
   * Returns the path of the cache file for a given key
   *
   * @param string $key
   * @access protected
   * @return string
   */
  protected function _getCacheKey($key) {
    return CACHE_DIR . DIRECTORY_SEPARATOR . $key;
  }

}
