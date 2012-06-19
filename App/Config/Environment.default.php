<?php
$environment = array(
  'mode' => 'dev', // dev | prod
  'database' => array(
    'default' => array(
      'dsn' => 'mysql:host=localhost;dbname=database_name;charset=UTF-8',
      'user' => '',
      'pass' => '',
      'options' => array(),
      'attributes' => array(
      ),
    ),
  ),
  'cache' => array(
    'engine' => '\Fabric\Cache\FileCache',
    'cache_requests' => false,
    'cache_dir' => CACHE_DIR,
  ),
  // 'cache' => array(
  //   'engine' => '\Fabric\Cache\MemCache',
  //   'servers' => array(
  //     array(
  //       'host'   => '127.0.0.1',
  //       'port'   => 11211,
  //       'weight' => 60,
  //     )
  //   )
  // ),
  // 'cache' => array(
  //   'engine' => '\Fabric\Cache\WinCache',
  //   'expire' => 10
  // ),
  'combine_assets' => false,
  'asset_host' => '', // $_SERVER['HTTP_HOST']
  'asset_timestamp' => '1234567890', //time(),
  'asset_filename_timestamp' => true, // Use filename timestamping for cache busting.


);
