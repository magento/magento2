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
 * @package    Zend_Server
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Server methods metadata
 *
 * @todo       Implement iterator
 * @category   Zend
 * @package    Zend_Server
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Server_Definition implements Countable, Iterator
{
    /**
     * @var array Array of Zend_Server_Method_Definition objects
     */
    protected $_methods = array();

    /**
     * @var bool Whether or not overwriting existing methods is allowed
     */
    protected $_overwriteExistingMethods = false;

    /**
     * Constructor
     *
     * @param  null|array $methods
     * @return void
     */
    public function __construct($methods = null)
    {
        if (is_array($methods)) {
            $this->setMethods($methods);
        }
    }

    /**
     * Set flag indicating whether or not overwriting existing methods is allowed
     *
     * @param mixed $flag
     * @return void
     */
    public function setOverwriteExistingMethods($flag)
    {
        $this->_overwriteExistingMethods = (bool) $flag;
        return $this;
    }

    /**
     * Add method to definition
     *
     * @param  array|Zend_Server_Method_Definition $method
     * @param  null|string $name
     * @return Zend_Server_Definition
     * @throws Zend_Server_Exception if duplicate or invalid method provided
     */
    public function addMethod($method, $name = null)
    {
        if (is_array($method)) {
            #require_once 'Zend/Server/Method/Definition.php';
            $method = new Zend_Server_Method_Definition($method);
        } elseif (!$method instanceof Zend_Server_Method_Definition) {
            #require_once 'Zend/Server/Exception.php';
            throw new Zend_Server_Exception('Invalid method provided');
        }

        if (is_numeric($name)) {
            $name = null;
        }
        if (null !== $name) {
            $method->setName($name);
        } else {
            $name = $method->getName();
        }
        if (null === $name) {
            #require_once 'Zend/Server/Exception.php';
            throw new Zend_Server_Exception('No method name provided');
        }

        if (!$this->_overwriteExistingMethods && array_key_exists($name, $this->_methods)) {
            #require_once 'Zend/Server/Exception.php';
            throw new Zend_Server_Exception(sprintf('Method by name of "%s" already exists', $name));
        }
        $this->_methods[$name] = $method;
        return $this;
    }

    /**
     * Add multiple methods
     *
     * @param  array $methods Array of Zend_Server_Method_Definition objects or arrays
     * @return Zend_Server_Definition
     */
    public function addMethods(array $methods)
    {
        foreach ($methods as $key => $method) {
            $this->addMethod($method, $key);
        }
        return $this;
    }

    /**
     * Set all methods at once (overwrite)
     *
     * @param  array $methods Array of Zend_Server_Method_Definition objects or arrays
     * @return Zend_Server_Definition
     */
    public function setMethods(array $methods)
    {
        $this->clearMethods();
        $this->addMethods($methods);
        return $this;
    }

    /**
     * Does the definition have the given method?
     *
     * @param  string $method
     * @return bool
     */
    public function hasMethod($method)
    {
        return array_key_exists($method, $this->_methods);
    }

    /**
     * Get a given method definition
     *
     * @param  string $method
     * @return null|Zend_Server_Method_Definition
     */
    public function getMethod($method)
    {
        if ($this->hasMethod($method)) {
            return $this->_methods[$method];
        }
        return false;
    }

    /**
     * Get all method definitions
     *
     * @return array Array of Zend_Server_Method_Definition objects
     */
    public function getMethods()
    {
        return $this->_methods;
    }

    /**
     * Remove a method definition
     *
     * @param  string $method
     * @return Zend_Server_Definition
     */
    public function removeMethod($method)
    {
        if ($this->hasMethod($method)) {
            unset($this->_methods[$method]);
        }
        return $this;
    }

    /**
     * Clear all method definitions
     *
     * @return Zend_Server_Definition
     */
    public function clearMethods()
    {
        $this->_methods = array();
        return $this;
    }

    /**
     * Cast definition to an array
     *
     * @return array
     */
    public function toArray()
    {
        $methods = array();
        foreach ($this->getMethods() as $key => $method) {
            $methods[$key] = $method->toArray();
        }
        return $methods;
    }

    /**
     * Countable: count of methods
     *
     * @return int
     */
    public function count()
    {
        return count($this->_methods);
    }

    /**
     * Iterator: current item
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->_methods);
    }

    /**
     * Iterator: current item key
     *
     * @return int|string
     */
    public function key()
    {
        return key($this->_methods);
    }

    /**
     * Iterator: advance to next method
     *
     * @return void
     */
    public function next()
    {
        return next($this->_methods);
    }

    /**
     * Iterator: return to first method
     *
     * @return void
     */
    public function rewind()
    {
        return reset($this->_methods);
    }

    /**
     * Iterator: is the current index valid?
     *
     * @return bool
     */
    public function valid()
    {
        return (bool) $this->current();
    }
}
