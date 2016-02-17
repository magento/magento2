<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Controller_Action_HelperBroker_PriorityStack
 */
#require_once 'Zend/Controller/Action/HelperBroker/PriorityStack.php';

/**
 * @see Zend_Loader
 */
#require_once 'Zend/Loader.php';

/**
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Action_HelperBroker
{
    /**
     * $_actionController - ActionController reference
     *
     * @var Zend_Controller_Action
     */
    protected $_actionController;

    /**
     * @var Zend_Loader_PluginLoader_Interface
     */
    protected static $_pluginLoader;

    /**
     * $_helpers - Helper array
     *
     * @var Zend_Controller_Action_HelperBroker_PriorityStack
     */
    protected static $_stack = null;

    /**
     * Set PluginLoader for use with broker
     *
     * @param  Zend_Loader_PluginLoader_Interface $loader
     * @return void
     */
    public static function setPluginLoader($loader)
    {
        if ((null !== $loader) && (!$loader instanceof Zend_Loader_PluginLoader_Interface)) {
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception('Invalid plugin loader provided to HelperBroker');
        }
        self::$_pluginLoader = $loader;
    }

    /**
     * Retrieve PluginLoader
     *
     * @return Zend_Loader_PluginLoader
     */
    public static function getPluginLoader()
    {
        if (null === self::$_pluginLoader) {
            #require_once 'Zend/Loader/PluginLoader.php';
            self::$_pluginLoader = new Zend_Loader_PluginLoader(array(
                'Zend_Controller_Action_Helper' => 'Zend/Controller/Action/Helper/',
            ));
        }
        return self::$_pluginLoader;
    }

    /**
     * addPrefix() - Add repository of helpers by prefix
     *
     * @param string $prefix
     */
    static public function addPrefix($prefix)
    {
        $prefix = rtrim($prefix, '_');
        $path   = str_replace('_', DIRECTORY_SEPARATOR, $prefix);
        self::getPluginLoader()->addPrefixPath($prefix, $path);
    }

    /**
     * addPath() - Add path to repositories where Action_Helpers could be found.
     *
     * @param string $path
     * @param string $prefix Optional; defaults to 'Zend_Controller_Action_Helper'
     * @return void
     */
    static public function addPath($path, $prefix = 'Zend_Controller_Action_Helper')
    {
        self::getPluginLoader()->addPrefixPath($prefix, $path);
    }

    /**
     * addHelper() - Add helper objects
     *
     * @param Zend_Controller_Action_Helper_Abstract $helper
     * @return void
     */
    static public function addHelper(Zend_Controller_Action_Helper_Abstract $helper)
    {
        self::getStack()->push($helper);
        return;
    }

    /**
     * resetHelpers()
     *
     * @return void
     */
    static public function resetHelpers()
    {
        self::$_stack = null;
        return;
    }

    /**
     * Retrieve or initialize a helper statically
     *
     * Retrieves a helper object statically, loading on-demand if the helper
     * does not already exist in the stack. Always returns a helper, unless
     * the helper class cannot be found.
     *
     * @param  string $name
     * @return Zend_Controller_Action_Helper_Abstract
     */
    public static function getStaticHelper($name)
    {
        $name  = self::_normalizeHelperName($name);
        $stack = self::getStack();

        if (!isset($stack->{$name})) {
            self::_loadHelper($name);
        }

        return $stack->{$name};
    }

    /**
     * getExistingHelper() - get helper by name
     *
     * Static method to retrieve helper object. Only retrieves helpers already
     * initialized with the broker (either via addHelper() or on-demand loading
     * via getHelper()).
     *
     * Throws an exception if the referenced helper does not exist in the
     * stack; use {@link hasHelper()} to check if the helper is registered
     * prior to retrieving it.
     *
     * @param  string $name
     * @return Zend_Controller_Action_Helper_Abstract
     * @throws Zend_Controller_Action_Exception
     */
    public static function getExistingHelper($name)
    {
        $name  = self::_normalizeHelperName($name);
        $stack = self::getStack();

        if (!isset($stack->{$name})) {
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception('Action helper "' . $name . '" has not been registered with the helper broker');
        }

        return $stack->{$name};
    }

    /**
     * Return all registered helpers as helper => object pairs
     *
     * @return array
     */
    public static function getExistingHelpers()
    {
        return self::getStack()->getHelpersByName();
    }

    /**
     * Is a particular helper loaded in the broker?
     *
     * @param  string $name
     * @return boolean
     */
    public static function hasHelper($name)
    {
        $name = self::_normalizeHelperName($name);
        return isset(self::getStack()->{$name});
    }

    /**
     * Remove a particular helper from the broker
     *
     * @param  string $name
     * @return boolean
     */
    public static function removeHelper($name)
    {
        $name = self::_normalizeHelperName($name);
        $stack = self::getStack();
        if (isset($stack->{$name})) {
            unset($stack->{$name});
        }

        return false;
    }

    /**
     * Lazy load the priority stack and return it
     *
     * @return Zend_Controller_Action_HelperBroker_PriorityStack
     */
    public static function getStack()
    {
        if (self::$_stack == null) {
            self::$_stack = new Zend_Controller_Action_HelperBroker_PriorityStack();
        }

        return self::$_stack;
    }

    /**
     * Constructor
     *
     * @param Zend_Controller_Action $actionController
     * @return void
     */
    public function __construct(Zend_Controller_Action $actionController)
    {
        $this->_actionController = $actionController;
        foreach (self::getStack() as $helper) {
            $helper->setActionController($actionController);
            $helper->init();
        }
    }

    /**
     * notifyPreDispatch() - called by action controller dispatch method
     *
     * @return void
     */
    public function notifyPreDispatch()
    {
        foreach (self::getStack() as $helper) {
            $helper->preDispatch();
        }
    }

    /**
     * notifyPostDispatch() - called by action controller dispatch method
     *
     * @return void
     */
    public function notifyPostDispatch()
    {
        foreach (self::getStack() as $helper) {
            $helper->postDispatch();
        }
    }

    /**
     * getHelper() - get helper by name
     *
     * @param  string $name
     * @return Zend_Controller_Action_Helper_Abstract
     */
    public function getHelper($name)
    {
        $name  = self::_normalizeHelperName($name);
        $stack = self::getStack();

        if (!isset($stack->{$name})) {
            self::_loadHelper($name);
        }

        $helper = $stack->{$name};

        $initialize = false;
        if (null === ($actionController = $helper->getActionController())) {
            $initialize = true;
        } elseif ($actionController !== $this->_actionController) {
            $initialize = true;
        }

        if ($initialize) {
            $helper->setActionController($this->_actionController)
                   ->init();
        }

        return $helper;
    }

    /**
     * Method overloading
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Zend_Controller_Action_Exception if helper does not have a direct() method
     */
    public function __call($method, $args)
    {
        $helper = $this->getHelper($method);
        if (!method_exists($helper, 'direct')) {
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception('Helper "' . $method . '" does not support overloading via direct()');
        }
        return call_user_func_array(array($helper, 'direct'), $args);
    }

    /**
     * Retrieve helper by name as object property
     *
     * @param  string $name
     * @return Zend_Controller_Action_Helper_Abstract
     */
    public function __get($name)
    {
        return $this->getHelper($name);
    }

    /**
     * Normalize helper name for lookups
     *
     * @param  string $name
     * @return string
     */
    protected static function _normalizeHelperName($name)
    {
        if (strpos($name, '_') !== false) {
            $name = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        }

        return ucfirst($name);
    }

    /**
     * Load a helper
     *
     * @param  string $name
     * @return void
     */
    protected static function _loadHelper($name)
    {
        try {
            $class = self::getPluginLoader()->load($name);
        } catch (Zend_Loader_PluginLoader_Exception $e) {
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception('Action Helper by name ' . $name . ' not found', 0, $e);
        }

        $helper = new $class();

        if (!$helper instanceof Zend_Controller_Action_Helper_Abstract) {
            #require_once 'Zend/Controller/Action/Exception.php';
            throw new Zend_Controller_Action_Exception('Helper name ' . $name . ' -> class ' . $class . ' is not of type Zend_Controller_Action_Helper_Abstract');
        }

        self::getStack()->push($helper);
    }
}
