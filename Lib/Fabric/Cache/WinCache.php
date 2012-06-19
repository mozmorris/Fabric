<?php
/**
 * @package Fabric
 */

namespace Fabric\Cache;

/**
 * WinCache class
 *
 **/
class WinCache extends \Fabric\Cache {

  /**
   * wincache expire (in seconds)
   *
   * @var string
   */
  private $_options = array(
    'expire' => 10,
  );


  /**
   * Check that the Wincache extension is available
   *
   * @param array $config
   */
  public function __construct($config) {
    $this->options = array_merge($this->_options, $config);
    if(!extension_loaded('wincache')) {
      /*
        TODO throw exceptions : no wincache!
      */
      die('no wincache');
    }
  }

  /**
   * @inheritdoc
   */
  public function write($key, $data, $config = 'default', $options = array()) {
    return wincache_ucache_set($key, $data, $this->_options['expire']);
  }

  /**
   * @inheritdoc
   */
  public function read($key) {
    return wincache_ucache_get($key);
  }

}
?>