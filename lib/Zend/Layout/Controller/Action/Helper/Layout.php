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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Layout.php 20096 2010-01-06 02:05:09Z bkarwin $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Controller_Action_Helper_Abstract */
#require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * Helper for interacting with Zend_Layout objects
 *
 * @uses       Zend_Controller_Action_Helper_Abstract
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Layout_Controller_Action_Helper_Layout extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Zend_Controller_Front
     */
    protected $_frontController;

    /**
     * @var Zend_Layout
     */
    protected $_layout;

    /**
     * @var bool
     */
    protected $_isActionControllerSuccessful = false;

    /**
     * Constructor
     *
     * @param  Zend_Layout $layout
     * @return void
     */
    public function __construct(Zend_Layout $layout = null)
    {
        if (null !== $layout) {
            $this->setLayoutInstance($layout);
        } else {
            /**
             * @see Zend_Layout
             */
            #require_once 'Zend/Layout.php';
            $layout = Zend_Layout::getMvcInstance();
        }

        if (null !== $layout) {
            $pluginClass = $layout->getPluginClass();
            $front = $this->getFrontController();
            if ($front->hasPlugin($pluginClass)) {
                $plugin = $front->getPlugin($pluginClass);
                $plugin->setLayoutActionHelper($this);
            }
        }
    }

    public function init()
    {
        $this->_isActionControllerSuccessful = false;
    }

    /**
     * Get front controller instance
     *
     * @return Zend_Controller_Front
     */
    public function getFrontController()
    {
        if (null === $this->_frontController) {
            /**
             * @see Zend_Controller_Front
             */
            #require_once 'Zend/Controller/Front.php';
            $this->_frontController = Zend_Controller_Front::getInstance();
        }

        return $this->_frontController;
    }

    /**
     * Get layout object
     *
     * @return Zend_Layout
     */
    public function getLayoutInstance()
    {
        if (null === $this->_layout) {
            /**
             * @see Zend_Layout
             */
            #require_once 'Zend/Layout.php';
            if (null === ($this->_layout = Zend_Layout::getMvcInstance())) {
                $this->_layout = new Zend_Layout();
            }
        }

        return $this->_layout;
    }

    /**
     * Set layout object
     *
     * @param  Zend_Layout $layout
     * @return Zend_Layout_Controller_Action_Helper_Layout
     */
    public function setLayoutInstance(Zend_Layout $layout)
    {
        $this->_layout = $layout;
        return $this;
    }

    /**
     * Mark Action Controller (according to this plugin) as Running successfully
     *
     * @return Zend_Layout_Controller_Action_Helper_Layout
     */
    public function postDispatch()
    {
        $this->_isActionControllerSuccessful = true;
        return $this;
    }

    /**
     * Did the previous action successfully complete?
     *
     * @return bool
     */
    public function isActionControllerSuccessful()
    {
        return $this->_isActionControllerSuccessful;
    }

    /**
     * Strategy pattern; call object as method
     *
     * Returns layout object
     *
     * @return Zend_Layout
     */
    public function direct()
    {
        return $this->getLayoutInstance();
    }

    /**
     * Proxy method calls to layout object
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $layout = $this->getLayoutInstance();
        if (method_exists($layout, $method)) {
            return call_user_func_array(array($layout, $method), $args);
        }

        #require_once 'Zend/Layout/Exception.php';
        throw new Zend_Layout_Exception(sprintf("Invalid method '%s' called on layout action helper", $method));
    }
}
