<?php
/**
 * @package Fabric
 */

namespace Fabric;

/**
 * Cache
 *
 * Abstract class for cache operations. Concrete classes should extend this
 * class and implement the read() and write() methods.
 *
 * Provides utility methods available for use in the concrete cache classes for
 * creating and parsing the data to write into the cache, and determining
 * whether cache objects have expired
 *
 * @abstract
 * @todo Allow configs to be set in the applications config files
 * @todo Implement Early Cache Rebuild, e.g. http://artur.ejsmont.org/blog/content/using-early-cache-rebuild-to-optimize-web-cache-performance
 */
abstract class Cache {

  /**
   * Stores regularly used cache configuration settings
   *
   * @var array
   * @access protected
   */
	protected $_config = array(
		'default' => array(
			'duration' => 5,
			'earlyExpires' => false,
		),
	);

  /**
   * Writes data to cache
   *
   * In addition to storing the passed data in the cache, you can also store
   * the expiry timestamp of the cache data, and the early expires timestamp.
   *
   * @param string $key The cache key the data should be associated with
   * @param mixed $data The data to cache
   * @param string $config The cache config to use with this data
   * @param array $options Used to override specific options associated with the
   * specified cache config
   * @abstract
   * @access public
   * @return boolean
   */
	abstract public function write($key, $data, $config = 'default', $options = array());

  /**
   * Reads data from the cache
   *
   * Should normally return the data if it hasn't expired or false if it has
   *
   * @param string $key
   * @abstract
   * @access public
   * @return mixed
   */
	abstract public function read($key);

  /**
   * Returns the expiry timestamp for the data based on the current time and
   * the given duration.
   *
   * @param integer $duration In seconds, e.g. 60
   * @access protected
   * @return integer
   */
	protected function _expires($duration) {
		return $this->_time + $duration;
	}

  /**
   * Determines whether the cache data has expired based on the current time and
   * the given expires timestamp (usually read from the data)
   *
   * @param integer $expires
   * @access protected
   * @return boolean
   */
	protected function _expired($expires) {
		return $this->_time > $expires;
	}

  /**
   * Returns the timestamp at which point the data should possibly be rebuilt
   * early.
   *
   * @param integer $duration
   * @param integer $earlyExpires
   * @access protected
   * @return integer
   */
	protected function _earlyExpires($duration, $earlyExpires) {
		return $this->_time + $duration - $earlyExpires;
	}

  /**
   * Determines whether the cache data should be considered for being rebuilt
   * early.
   *
   * @param integer $expires
   * @param integer $earlyExpires
   * @access protected
   * @return boolean
   */
	protected function _earlyExpired($expires, $earlyExpires) {
		return $this->_time > $expires - $earlyExpires;
	}

  /**
   * Returns a string containing the expiry timestamp, an optional early rebuild
   * timestamp and then, on a new line, the data to be cached, serialized.
   *
   * @param mixed $data
   * @param array $options
   * @access protected
   * @return string
   */
	protected function _createData($data, $options) {
		$content = $this->_expires($options['duration']);
		if (!empty($options['earlyExpires'])) {
			$content .= $this->_earlyExpires($options['duration'], $options['earlyExpires']);
		}
		$content .= "\n" . serialize($data);
		return $content;
	}

  /**
   * Returns an array of the expires timestamp, optional early rebuild timestamp
   * and the data, unserialized, from a given data string read from the cache.
   *
   * @param string $data
   * @access protected
   * @return array
   */
	protected function _parseData($data) {
		list($expires, $data) = explode("\n", $data, 2);
		$earlyExpires = false;
		if (strlen($expires) > 10) {
			list($expires, $earlyExpires) = str_split($expires, 10);
		}
		$data = unserialize($data);
		return array($expires, $earlyExpires, $data);
	}

}
