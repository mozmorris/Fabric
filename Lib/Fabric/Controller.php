<?php
/**
 * @package Fabric
 */

namespace Fabric;

/**
 * Controller
 *
 * Base Controller class which application controllers should extend. Provides
 * methods for lazy loading dependencies.
 */
class Controller {

  /**
   * Dependency Injection Container object
   *
   * @var Pimple
   * @access private
   */
  private $__Container;

  /**
   * Config object
   *
   * @var \Fabric\Config
   * @access protected
   */
  protected $_Config;

  /**
   * Request object
   *
   * @var \Fabric\Request
   * @access protected
   */
  protected $_Request;

  /**
   * Array of \Fabric\Model objects
   *
   * @var array
   * @access protected
   */
  protected $_models = array();

  /**
   * The template that should be used to render the data fetched by the
   * controller action. By default, the _getTemplate method will automatically
   * determine the template to be used based on the controller action, but you
   * can override that by setting this property during the action.
   *
   * @var string
   * @access protected
   */
  protected $_template;

  /**
   * The layout that should be used to wrap the view. The default is:
   * App/Views/Layouts/Default.php. You can override this by setting this
   * property within the controller action. The format should be CamelCase as
   * should the filename.
   *
   * @var string
   * @access protected
   */
  protected $_layout = 'Default.php';

  /**
   * I18n object
   *
   * @var \Fabric\I18n
   * @access protected
   */
  protected $_I18n;

  /**
   * Called when the controller is instantiated which is usually just the once
   * when the Request has been parsed by the Router and the controller and
   * action have been identified. Usually, just the Dependency Injection
   * Container object is passed in, but all dependencies can be passed in to
   * facilitate Unit Testing.
   *
   * @param \Pimple $Container
   * @param \Fabric\Config $Config
   * @param \Fabric\Request $Request
   * @param array $models
   * @access public
   * @return void
   */
  public function __construct($Container = null, $Config = null, $Request = null, $models = array()) {
    $this->__Container = $Container;
    $this->_Config = $Config;
    $this->_Request = $Request;
    $this->_models = $models;
  }

  /**
   * Returns the \Fabric\Config object
   *
   * If the object is already available as a property of the controller, that is
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
   * Returns the object of the requested sub class of \Fabric\Model
   *
   * If the object is already available in the $_models property of the
   * controller, that is returned, else it is loaded lazily from the Dependency
   * Injection Container.
   *
   * @param string $name
   * @access protected
   * @return \Fabric\Model
   */
  protected function _getModel($name) {
    if (array_key_exists($name, $this->_models)) {
      return $this->_models[$name];
    }
    return $this->_models[$name] = $this->__Container['Model']($this->__Container, $name);
  }

  /**
   * If the object is already available as a property of the controller, that is
   * returned, else it is loaded lazily from the Dependency Injection Container.
   *
   * @access protected
   * @return \Fabric\Request
   */
  protected function _getRequest() {
    if ($this->_Request) {
      return $this->_Request;
    }
    return $this->_Request = $this->__Container['Request'];
  }

  /**
   * Returns the path to the template file to be rendered by the View
   *
   * The returned path is relative to the App/Views directory
   *
   * If manually specified in the controller action, the full the full relative
   * path should be set, e.g. Post/View.php
   *
   * If note explicitly set, the method automatically determines the appropriate
   * path based on the controller name, the extension of the request and the
   * action, which has the first letter converted to uppercase. In addition,
   * .php is always added. E.g. for controller PostCategory, action tagCloud,
   * the default view would be PostCategory/TagCloud.php which should be in the
   * App/View directory.
   *
   * @param string $action
   * @access public
   * @return string
   */
  public function getTemplate($action = null) {

    if ($this->_template) {
      return $this->_template;
    }

    $controller = get_called_class();

    $tmp = (explode('\\', $controller));
    $controllerName = end($tmp);

    $template = $controllerName . DIRECTORY_SEPARATOR;

    if ($this->_getRequest()->ext) {
      $template .= $this->_getRequest()->ext . DIRECTORY_SEPARATOR;
    }

    $template .= ucfirst($action) . '.php';

    return $template;

  }

  /**
   * Returns a string path to the layout file to use to wrap the rendered view
   * in.
   *
   * The default is the Layout/Default.php file in App/View directory.
   *
   * If you don't want to use a layout, set the $_layout property of your
   * controller to false somewhere in your controller action.
   *
   * All layout files should be in the App/View/Layout directory.
   *
   * If the request has an extension, the file should be located in the sub
   * directory of the Layout directory, named for the extension, but the the
   * first letter converted to upper case. E.g. for /posts.rss, the layout
   * should be in App/View/Layout/Rss/Default.php
   *
   * @access public
   * @return string
   */
  public function getLayout() {

    if (!$this->_layout) {
      return false;
    }

    $layout = 'Layout' . DIRECTORY_SEPARATOR;

    if ($this->_getRequest()->ext) {
      $layout .= ucfirst($this->_getRequest()->ext) . DIRECTORY_SEPARATOR;
    }

    $layout .= $this->_layout;

    return $layout;

  }

  /**
   * Returns I18n object
   *
   * @return \Fabric\I18n
   */
  protected function _getI18n() {
    if ($this->_I18n) {
      return $this->_I18n;
    }

    return $this->_I18n = $this->__Container['I18n'];
  }


}
