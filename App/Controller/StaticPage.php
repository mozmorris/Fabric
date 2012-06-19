<?php

namespace App\Controller;

class StaticPage extends Controller {

  public function home($params) {

    return array(
      'fabric' => 'I am Fabric.',
      'meta_title' => $this->_getConfig()->common['meta_data']['title']
    );
  }
}
