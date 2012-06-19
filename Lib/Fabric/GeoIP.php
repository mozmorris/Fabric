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
class GeoIP {

  /**
   * continent
   *
   * @var string
   * @access public
   */
  public $continent = 'eu';

  /**
   * redirectUrl
   *
   * @var string
   * @access public
   */
  public $redirectUrl = false;

  /**
   * territory
   *
   * @var string
   * @access public
   */
  public $territory = 'uk';

  /**
   * territories
   *
   * @var array
   * @access public
   */
  public $territories = array();


  /**
   * Stores the arguments in the class properties and initiates the GeoIP lookup
   * if config redirect is set to true
   *
   * @param string $locale
   * @access public
   * @return void
   */
  public function __construct(array $config, $uri, $host) {
    $this->territory = $config['default_territory'];
    $this->territories = $config['territories'];
    
    if (!empty($config['redirect']) && $uri == '/') {
      $this->redirectUrl = $this->_getPathForUser($host);
    }
  }

  /**
   * getPathForUser
   *
   * @param string $host
   * @return string
   * @access private
   */
  private function _getPathForUser($host = 'www.google.co.uk')
  {
    // get country code for host
    $countryCode = $this->_getUserCountryCode($host);

    // if code is in territories return it
    if (($key = array_search($countryCode, $this->territories)) !== false) {
      return $this->territories[$key];
    } else {

      // get the continent code for the current user
      $continentCode = $this->_getUserContinentCode($host);

      // if North or South America, then return 'us'
      if ($continentCode == 'na' || $continentCode == 'sa') {
        return 'us';
      }

      return $this->territory;
    }
  }

  /**
   * _getUserCountryCode
   *
   * @param string $host
   * @return string
   * @access private
   */
  private function _getUserCountryCode($host)
  {
    // get the country code based on ip address or host name
    $countryCode = @geoip_country_code_by_name($host);

    // return the country code. if code is null,
    return ($countryCode ? strtolower($countryCode) : $this->territory);
  }

  /**
   * _getUserContinentCode function
   *
   * @param string $host
   * @return string
   * @access private
   **/
  private function _getUserContinentCode($host) {
    // get the continent code based on ip address or host name
    $continentCode = @geoip_continent_code_by_name($host);

    // return the continent code. if code is null,
    return ($continentCode ? strtolower($continentCode) : $this->continent);
  }




}
