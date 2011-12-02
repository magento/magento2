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
 * @subpackage Method
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Definition.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Method definition metadata
 *
 * @category   Zend
 * @package    Zend_Server
 * @subpackage Method
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Server_Method_Definition
{
    /**
     * @var Zend_Server_Method_Callback
     */
    protected $_callback;

    /**
     * @var array
     */
    protected $_invokeArguments = array();

    /**
     * @var string
     */
    protected $_methodHelp = '';

    /**
     * @var string
     */
    protected $_name;

    /**
     * @var null|object
     */
    protected $_object;

    /**
     * @var array Array of Zend_Server_Method_Prototype objects
     */
    protected $_prototypes = array();

    /**
     * Constructor
     *
     * @param  null|array $options
     * @return void
     */
    public function __construct($options = null)
    {
        if ((null !== $options) && is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set object state from options
     *
     * @param  array $options
     * @return Zend_Server_Method_Definition
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set method name
     *
     * @param  string $name
     * @return Zend_Server_Method_Definition
     */
    public function setName($name)
    {
        $this->_name = (string) $name;
        return $this;
    }

    /**
     * Get method name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set method callback
     *
     * @param  array|Zend_Server_Method_Callback $callback
     * @return Zend_Server_Method_Definition
     */
    public function setCallback($callback)
    {
        if (is_array($callback)) {
            #require_once 'Zend/Server/Method/Callback.php';
            $callback = new Zend_Server_Method_Callback($callback);
        } elseif (!$callback instanceof Zend_Server_Method_Callback) {
            #require_once 'Zend/Server/Exception.php';
            throw new Zend_Server_Exception('Invalid method callback provided');
        }
        $this->_callback = $callback;
        return $this;
    }

    /**
     * Get method callback
     *
     * @return Zend_Server_Method_Callback
     */
    public function getCallback()
    {
        return $this->_callback;
    }

    /**
     * Add prototype to method definition
     *
     * @param  array|Zend_Server_Method_Prototype $prototype
     * @return Zend_Server_Method_Definition
     */
    public function addPrototype($prototype)
    {
        if (is_array($prototype)) {
            #require_once 'Zend/Server/Method/Prototype.php';
            $prototype = new Zend_Server_Method_Prototype($prototype);
        } elseif (!$prototype instanceof Zend_Server_Method_Prototype) {
            #require_once 'Zend/Server/Exception.php';
            throw new Zend_Server_Exception('Invalid method prototype provided');
        }
        $this->_prototypes[] = $prototype;
        return $this;
    }

    /**
     * Add multiple prototypes at once
     *
     * @param  array $prototypes Array of Zend_Server_Method_Prototype objects or arrays
     * @return Zend_Server_Method_Definition
     */
    public function addPrototypes(array $prototypes)
    {
        foreach ($prototypes as $prototype) {
            $this->addPrototype($prototype);
        }
        return $this;
    }

    /**
     * Set all prototypes at once (overwrites)
     *
     * @param  array $prototypes Array of Zend_Server_Method_Prototype objects or arrays
     * @return Zend_Server_Method_Definition
     */
    public function setPrototypes(array $prototypes)
    {
        $this->_prototypes = array();
        $this->addPrototypes($prototypes);
        return $this;
    }

    /**
     * Get all prototypes
     *
     * @return array $prototypes Array of Zend_Server_Method_Prototype objects or arrays
     */
    public function getPrototypes()
    {
        return $this->_prototypes;
    }

    /**
     * Set method help
     *
     * @param  string $methodHelp
     * @return Zend_Server_Method_Definition
     */
    public function setMethodHelp($methodHelp)
    {
        $this->_methodHelp = (string) $methodHelp;
        return $this;
    }

    /**
     * Get method help
     *
     * @return string
     */
    public function getMethodHelp()
    {
        return $this->_methodHelp;
    }

    /**
     * Set object to use with method calls
     *
     * @param  object $object
     * @return Zend_Server_Method_Definition
     */
    public function setObject($object)
    {
        if (!is_object($object) && (null !== $object)) {
            #require_once 'Zend/Server/Exception.php';
            throw new Zend_Server_Exception('Invalid object passed to ' . __CLASS__ . '::' . __METHOD__);
        }
        $this->_object = $object;
        return $this;
    }

    /**
     * Get object to use with method calls
     *
     * @return null|object
     */
    public function getObject()
    {
        return $this->_object;
    }

    /**
     * Set invoke arguments
     *
     * @param  array $invokeArguments
     * @return Zend_Server_Method_Definition
     */
    public function setInvokeArguments(array $invokeArguments)
    {
        $this->_invokeArguments = $invokeArguments;
        return $this;
    }

    /**
     * Retrieve invoke arguments
     *
     * @return array
     */
    public function getInvokeArguments()
    {
        return $this->_invokeArguments;
    }

    /**
     * Serialize to array
     *
     * @return array
     */
    public function toArray()
    {
        $prototypes = $this->getPrototypes();
        $signatures = array();
        foreach ($prototypes as $prototype) {
            $signatures[] = $prototype->toArray();
        }

        return array(
            'name'            => $this->getName(),
            'callback'        => $this->getCallback()->toArray(),
            'prototypes'      => $signatures,
            'methodHelp'      => $this->getMethodHelp(),
            'invokeArguments' => $this->getInvokeArguments(),
            'object'          => $this->getObject(),
        );
    }
}
