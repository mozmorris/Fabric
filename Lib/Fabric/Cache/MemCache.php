<?php
/**
 * @package Fabric
 */

namespace Fabric\Cache;

/**
 * MemCache class
 *
 **/
class MemCache extends \Fabric\Cache {

  /**
   * instance of Memcache
   *
   * @var object
   * @access private
   */
  private $_cache = null;

  /**
   * defined servers from config
   *
   * @var array
   * @access private
   */
  private $_servers = array();

  /**
   * default addServer memcache options
   *
   * @link http://uk3.php.net/manual/en/memcache.addserver.php
   * @var string
   */
  private $_options = array(
    'host'          => 'localhost',
    'port'          => 11211,
    'persistent'    => true,
    'weight'        => 1,
    'timeout'       => 1,
    'retryInterval' => 15,
    'status'        => true,
    'key_prefix'    => ''
  );

  /**
   * instantiates Memcache and adds servers from the passed config
   *
   * @param array $config
   */
  public function __construct($config) {
    $this->_cache = new \Memcache;

    if (!empty($config['servers'])) {
      foreach ($config['servers'] as $key => $server) {
        $options = array_merge($this->_options, $server);
        $this->_cache->addServer($options['host'], $options['port'], $options['persistent'], $options['weight'], $options['timeout'], $options['retryInterval'], $options['status']);
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function write($key, $data, $config = 'default', $options = array()) {
    return $this->_cache->set($this->_options['key_prefix'] . $key, $data, 0, 5);
  }

  /**
   * @inheritdoc
   */
  public function read($key) {
    return $this->_cache->get($this->_options['key_prefix'] . $key);
  }

}
?>