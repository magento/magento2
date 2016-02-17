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
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Controller_Router_Interface */
#require_once 'Zend/Controller/Router/Interface.php';

/**
 * Simple first implementation of a router, to be replaced
 * with rules-based URI processor.
 *
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Controller_Router_Abstract implements Zend_Controller_Router_Interface
{
    /**
     * URI delimiter
     */
    const URI_DELIMITER = '/';

    /**
     * Front controller instance
     *
     * @var Zend_Controller_Front
     */
    protected $_frontController;

    /**
     * Array of invocation parameters to use when instantiating action
     * controllers
     *
     * @var array
     */
    protected $_invokeParams = array();

    /**
     * Constructor
     *
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->setParams($params);
    }

    /**
     * Add or modify a parameter to use when instantiating an action controller
     *
     * @param string $name
     * @param mixed  $value
     * @return Zend_Controller_Router_Abstract
     */
    public function setParam($name, $value)
    {
        $name                       = (string)$name;
        $this->_invokeParams[$name] = $value;

        return $this;
    }

    /**
     * Set parameters to pass to action controller constructors
     *
     * @param array $params
     * @return Zend_Controller_Router_Abstract
     */
    public function setParams(array $params)
    {
        $this->_invokeParams = array_merge($this->_invokeParams, $params);

        return $this;
    }

    /**
     * Retrieve a single parameter from the controller parameter stack
     *
     * @param string $name
     * @return mixed
     */
    public function getParam($name)
    {
        if (isset($this->_invokeParams[$name])) {
            return $this->_invokeParams[$name];
        }

        return null;
    }

    /**
     * Retrieve action controller instantiation parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_invokeParams;
    }

    /**
     * Clear the controller parameter stack
     *
     * By default, clears all parameters. If a parameter name is given, clears
     * only that parameter; if an array of parameter names is provided, clears
     * each.
     *
     * @param null|string|array single key or array of keys for params to clear
     * @return Zend_Controller_Router_Abstract
     */
    public function clearParams($name = null)
    {
        if (null === $name) {
            $this->_invokeParams = array();
        } elseif (is_string($name) && isset($this->_invokeParams[$name])) {
            unset($this->_invokeParams[$name]);
        } elseif (is_array($name)) {
            foreach ($name as $key) {
                if (is_string($key) && isset($this->_invokeParams[$key])) {
                    unset($this->_invokeParams[$key]);
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve Front Controller
     *
     * @return Zend_Controller_Front
     */
    public function getFrontController()
    {
        // Used cache version if found
        if (null !== $this->_frontController) {
            return $this->_frontController;
        }

        #require_once 'Zend/Controller/Front.php';
        $this->_frontController = Zend_Controller_Front::getInstance();

        return $this->_frontController;
    }

    /**
     * Set Front Controller
     *
     * @param Zend_Controller_Front $controller
     * @return Zend_Controller_Router_Interface
     */
    public function setFrontController(Zend_Controller_Front $controller)
    {
        $this->_frontController = $controller;

        return $this;
    }
}
