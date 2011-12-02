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
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Standalone.php 20143 2010-01-08 15:17:11Z matthew $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_View_Helper_Placeholder_Registry */
#require_once 'Zend/View/Helper/Placeholder/Registry.php';

/** Zend_View_Helper_Abstract.php */
#require_once 'Zend/View/Helper/Abstract.php';

/**
 * Base class for targetted placeholder helpers
 *
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_View_Helper_Placeholder_Container_Standalone extends Zend_View_Helper_Abstract implements IteratorAggregate, Countable, ArrayAccess
{
    /**
     * @var Zend_View_Helper_Placeholder_Container_Abstract
     */
    protected $_container;

    /**
     * @var Zend_View_Helper_Placeholder_Registry
     */
    protected $_registry;

    /**
     * Registry key under which container registers itself
     * @var string
     */
    protected $_regKey;

    /**
     * Flag wheter to automatically escape output, must also be
     * enforced in the child class if __toString/toString is overriden
     * @var book
     */
    protected $_autoEscape = true;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->setRegistry(Zend_View_Helper_Placeholder_Registry::getRegistry());
        $this->setContainer($this->getRegistry()->getContainer($this->_regKey));
    }

    /**
     * Retrieve registry
     *
     * @return Zend_View_Helper_Placeholder_Registry
     */
    public function getRegistry()
    {
        return $this->_registry;
    }

    /**
     * Set registry object
     *
     * @param  Zend_View_Helper_Placeholder_Registry $registry
     * @return Zend_View_Helper_Placeholder_Container_Standalone
     */
    public function setRegistry(Zend_View_Helper_Placeholder_Registry $registry)
    {
        $this->_registry = $registry;
        return $this;
    }

    /**
     * Set whether or not auto escaping should be used
     *
     * @param  bool $autoEscape whether or not to auto escape output
     * @return Zend_View_Helper_Placeholder_Container_Standalone
     */
    public function setAutoEscape($autoEscape = true)
    {
        $this->_autoEscape = ($autoEscape) ? true : false;
        return $this;
    }

    /**
     * Return whether autoEscaping is enabled or disabled
     *
     * return bool
     */
    public function getAutoEscape()
    {
        return $this->_autoEscape;
    }

    /**
     * Escape a string
     *
     * @param  string $string
     * @return string
     */
    protected function _escape($string)
    {
        $enc = 'UTF-8';
        if ($this->view instanceof Zend_View_Interface
            && method_exists($this->view, 'getEncoding')
        ) {
            $enc = $this->view->getEncoding();
        }

        return htmlspecialchars((string) $string, ENT_COMPAT, $enc);
    }

    /**
     * Set container on which to operate
     *
     * @param  Zend_View_Helper_Placeholder_Container_Abstract $container
     * @return Zend_View_Helper_Placeholder_Container_Standalone
     */
    public function setContainer(Zend_View_Helper_Placeholder_Container_Abstract $container)
    {
        $this->_container = $container;
        return $this;
    }

    /**
     * Retrieve placeholder container
     *
     * @return Zend_View_Helper_Placeholder_Container_Abstract
     */
    public function getContainer()
    {
        return $this->_container;
    }

    /**
     * Overloading: set property value
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $container = $this->getContainer();
        $container[$key] = $value;
    }

    /**
     * Overloading: retrieve property
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        $container = $this->getContainer();
        if (isset($container[$key])) {
            return $container[$key];
        }

        return null;
    }

    /**
     * Overloading: check if property is set
     *
     * @param  string $key
     * @return bool
     */
    public function __isset($key)
    {
        $container = $this->getContainer();
        return isset($container[$key]);
    }

    /**
     * Overloading: unset property
     *
     * @param  string $key
     * @return void
     */
    public function __unset($key)
    {
        $container = $this->getContainer();
        if (isset($container[$key])) {
            unset($container[$key]);
        }
    }

    /**
     * Overload
     *
     * Proxy to container methods
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $container = $this->getContainer();
        if (method_exists($container, $method)) {
            $return = call_user_func_array(array($container, $method), $args);
            if ($return === $container) {
                // If the container is returned, we really want the current object
                return $this;
            }
            return $return;
        }

        #require_once 'Zend/View/Exception.php';
        $e = new Zend_View_Exception('Method "' . $method . '" does not exist');
        $e->setView($this->view);
        throw $e;
    }

    /**
     * String representation
     *
     * @return string
     */
    public function toString()
    {
        return $this->getContainer()->toString();
    }

    /**
     * Cast to string representation
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Countable
     *
     * @return int
     */
    public function count()
    {
        $container = $this->getContainer();
        return count($container);
    }

    /**
     * ArrayAccess: offsetExists
     *
     * @param  string|int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->getContainer()->offsetExists($offset);
    }

    /**
     * ArrayAccess: offsetGet
     *
     * @param  string|int $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getContainer()->offsetGet($offset);
    }

    /**
     * ArrayAccess: offsetSet
     *
     * @param  string|int $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        return $this->getContainer()->offsetSet($offset, $value);
    }

    /**
     * ArrayAccess: offsetUnset
     *
     * @param  string|int $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        return $this->getContainer()->offsetUnset($offset);
    }

    /**
     * IteratorAggregate: get Iterator
     *
     * @return Iterator
     */
    public function getIterator()
    {
        return $this->getContainer()->getIterator();
    }
}
