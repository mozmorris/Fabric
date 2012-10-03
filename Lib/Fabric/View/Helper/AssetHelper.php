<?php
/**
 * @package Fabric
 */

namespace Fabric\View\Helper;

/**
 * Helper
 *
 * @uses Helper
 */
class AssetHelper extends \Fabric\View\Helper {

  /**
   * Helper object
   *
   * @var \Fabric\View\Helper
   * @access protected
   */
  protected $_Helper;

  /**
   * Config object
   *
   * @var \Fabric\Config
   * @access protected
   */
  protected $_Config;

  public function __construct(\Fabric\View\Helper $Helper = null, \Fabric\Config $Config = null) {

    $this->_Helper = $Helper;
    $this->_Config = $Config;

  }

  public function imageTag($path, $options = array()) {

    $attributes = ' alt="' . (empty($options['alt']) ? '' : $options['alt']) . '"';

    if (!empty($options['width'])) {
      $attributes .= ' width="' . $options['width'] . '"';
    }

    if (!empty($options['height'])) {
      $attributes .= ' height="' . $options['height'] . '"';
    }

    if (!empty($options['id'])) {
      $attributes .= ' id="' . $options['id'] . '"';
    }

    $image = sprintf('<img src="%s"%s/>', $this->imagePath($path), $attributes);

    return $image;
  }

  public function javascriptIncludeTag() {

    $paths = func_get_args();
    $options = is_array($paths[count($paths) - 1]) ? array_pop($paths) : array();
    $attributes = '';
    $combineAssets = array_key_exists('combine_assets', $this->_Config->environment) ? $this->_Config->environment['combine_assets'] : false;

    if ($combineAssets) {

      $assets = array();

      foreach ($paths as $path) {

        if (preg_match('/http|https/', $path)) {
          return $this->javascriptIncludeTags($paths, $attributes);
          break;
        }

        $path = $this->assetExtension($path, 'js');
        $path = $this->assetDirectory($path, 'javascripts');

        $assets[] = $path;
      }

      $combined = $this->combineAssets($assets, 'js');

      if (!$combined) {
        return $this->javascriptIncludeTags($paths, $attributes);
      } else {
        return $this->javascriptIncludeTags(array($combined), $attributes);
      }
    } else {
      return $this->javascriptIncludeTags($paths, $attributes);
    }
  }

  public function javascriptIncludeTags($paths, $attributes = '') {
    $tags = array();
    foreach ($paths as $path) {
      $tags[] = sprintf('<script src="%s"%s></script>', $this->javascriptPath($path), $attributes);
    }
    return implode("\n", $tags);
  }



  public function stylesheetLinkTag() {

    $paths = func_get_args();
    $options = is_array($paths[count($paths) - 1]) ? array_pop($paths) : array();
    $attributes = '';
    $combineAssets = array_key_exists('combine_assets', $this->_Config->environment) ? $this->_Config->environment['combine_assets'] : false;

    if (!empty($options['media'])) {
      $attributes = ' media="' . $options['media'] . '"';
    }

    if ($combineAssets) {

      $assets = array();

      foreach ($paths as $path) {

        if (preg_match('/http|https/', $path)) {
          return $this->stylesheetLinkTags($paths, $attributes);
          break;
        }

        $path = $this->assetExtension($path, 'css');
        $path = $this->assetDirectory($path, 'stylesheets');

        $assets[] = $path;
      }

      $combined = $this->combineAssets($assets, 'css');

      if (!$combined) {
        return $this->stylesheetLinkTags($paths, $attributes);
      } else {
        return $this->stylesheetLinkTags(array($combined), $attributes);
      }
    } else {
      return $this->stylesheetLinkTags($paths, $attributes);
    }
  }

  public function stylesheetLinkTags($paths, $attributes = '') {
    $tags = array();
    foreach ($paths as $path) {
      $tags[] = sprintf('<link rel="stylesheet" href="%s"%s>', $this->stylesheetPath($path), $attributes);
    }
    return implode("\n", $tags);
  }

  public function combineAssets($paths, $type) {

    $combinedFilename = "/assets/" . sha1(implode("&", $paths));

    if ($type == 'js') {
      $path = $this->assetExtension($combinedFilename, 'js');
      $path = $this->assetDirectory($path, 'javascripts');
    } else {
      $path = $this->assetExtension($combinedFilename, 'css');
      $path = $this->assetDirectory($path, 'stylesheets');
    }

    $combinedFilepath = WEB_ROOT . $this->assetTimestamp($path);

    if (file_exists($combinedFilepath)) {
      return $combinedFilename;
    } else {
      $files = array();
      foreach ($paths as $path) {
        if (file_exists(WEB_ROOT.$path)) {
          $files[] = WEB_ROOT.$path;
        } else {
          return false;
        }
      }

      $files = implode(' ', $files);
      $yuiCompressorPath = LIB_DIR.'/YUICompressor/yuicompressor-2.4.7.jar';
      $command = "cat {$files} >> {$combinedFilepath}; java -jar " . escapeshellarg($yuiCompressorPath) . " -o " . escapeshellarg($combinedFilepath) . " " . escapeshellarg($combinedFilepath);
      exec($command);
      return $combinedFilename;
    }

  }

  /* Compute a path to an asset.
   *
   * Returns full URLs untouched.
   * Adds the relevant asset extension if not present.
   * Prefix with the relevant asset directory if path lacks a leading /.
   * Rewrite the path to include cache-busting timestamp, if configured.
   * Rewrite the path to Include the asset host.
   */
  public function assetPath($path, $assetDirectory, $assetExtension = null) {

    if (preg_match('/http|https/', $path)) {
      return $path;
    }

    $path = $this->assetExtension($path, $assetExtension);
    $path = $this->assetDirectory($path, $assetDirectory);
    $path = $this->assetTimestamp($path);

    if (array_key_exists('asset_host', $this->_Config->environment)) {
      $path = $this->assetHost($path);
    }

    return $path;
  }

  public function assetDirectory($path, $assetDirectory) {
    if ($path[0] != '/') {
      $path = "/{$assetDirectory}/{$path}";
    }
    return $path;
  }

  public function assetExtension($path, $assetExtension) {
    $pathExtension = pathinfo($path, PATHINFO_EXTENSION);
    if (empty($pathExtension)) {
      $path .= ".{$assetExtension}";
    }
    return $path;
  }

  /* Add an asset host to a path.
   *
   * Use the assetHost config variable
   */
  public function assetHost($path){
    $assetHost = rtrim($this->_Config->environment['asset_host'], '/');
    return "//{$assetHost}{$path}";
  }

  /* Add a cache-busting timestamp to a path.
   *
   * If assetTimestamp config varible is set to true use the modified
   * time of the asset file at path as the timestamp, otherwise use the
   * value of the assetTimestamp config variable.
   *
   * If assetTimestamp config variable is false, or it is true but the
   * asset file at path can not be accessed then just return the unaltered path.
   *
   * If assetFilenameTimestamp config variable is true rewrite the path
   * to include the timestamp as part of the filename rather than as a query string.
   */
  public function assetTimestamp($path){

    $assetTimestamp = array_key_exists('asset_timestamp', $this->_Config->environment) ? $this->_Config->environment['asset_timestamp'] : false;

    if ($assetTimestamp === false) {
      return $path;
    } else if ($assetTimestamp === true) {
      if (file_exists(WEB_ROOT.$path)) {
        $assetTimestamp = filemtime(WEB_ROOT.$path);
      } else {
        return $path;
      }
    }

    $assetFilenameTimestamp = array_key_exists('asset_filename_timestamp', $this->_Config->environment) ? $this->_Config->environment['asset_filename_timestamp'] : false;

    $pathExtension = pathinfo($path, PATHINFO_EXTENSION);

    if (!empty($pathExtension) && $assetFilenameTimestamp) {
      return substr_replace($path, "{$assetTimestamp}.{$pathExtension}", strrpos($path, $pathExtension), strlen($pathExtension));
    } else {
      return "{$path}?{$assetTimestamp}";
    }
  }

  public function imagePath($path) {
    return $this->assetPath($path, 'images');
  }

  public function javascriptPath($path) {
    return $this->assetPath($path, 'javascripts', 'js');
  }

  public function stylesheetPath($path) {
    return $this->assetPath($path, 'stylesheets', 'css');
  }
}