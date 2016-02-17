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
 */

/** Zend_Server_Interface */
#require_once 'Zend/Server/Interface.php';

/**
 * Zend_Server_Definition
 */
#require_once 'Zend/Server/Definition.php';

/**
 * Zend_Server_Method_Definition
 */
#require_once 'Zend/Server/Method/Definition.php';

/**
 * Zend_Server_Method_Callback
 */
#require_once 'Zend/Server/Method/Callback.php';

/**
 * Zend_Server_Method_Prototype
 */
#require_once 'Zend/Server/Method/Prototype.php';

/**
 * Zend_Server_Method_Parameter
 */
#require_once 'Zend/Server/Method/Parameter.php';

/**
 * Zend_Server_Abstract
 *
 * @category   Zend
 * @package    Zend_Server
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
abstract class Zend_Server_Abstract implements Zend_Server_Interface
{
    /**
     * @deprecated
     * @var array List of PHP magic methods (lowercased)
     */
    protected static $magic_methods = array(
        '__call',
        '__clone',
        '__construct',
        '__destruct',
        '__get',
        '__isset',
        '__set',
        '__set_state',
        '__sleep',
        '__tostring',
        '__unset',
        '__wakeup',
    );

    /**
     * @var bool Flag; whether or not overwriting existing methods is allowed
     */
    protected $_overwriteExistingMethods = false;

    /**
     * @var Zend_Server_Definition
     */
    protected $_table;

    /**
     * Constructor
     *
     * Setup server description
     *
     * @return void
     */
    public function __construct()
    {
        $this->_table = new Zend_Server_Definition();
        $this->_table->setOverwriteExistingMethods($this->_overwriteExistingMethods);
    }

    /**
     * Returns a list of registered methods
     *
     * Returns an array of method definitions.
     *
     * @return Zend_Server_Definition
     */
    public function getFunctions()
    {
        return $this->_table;
    }

    /**
     * Lowercase a string
     *
     * Lowercase's a string by reference
     *
     * @deprecated
     * @param  string $string value
     * @param  string $key
     * @return string Lower cased string
     */
    public static function lowerCase(&$value, &$key)
    {
        trigger_error(__CLASS__ . '::' . __METHOD__ . '() is deprecated and will be removed in a future version', E_USER_NOTICE);
        return $value = strtolower($value);
    }

    /**
     * Build callback for method signature
     *
     * @param  Zend_Server_Reflection_Function_Abstract $reflection
     * @return Zend_Server_Method_Callback
     */
    protected function _buildCallback(Zend_Server_Reflection_Function_Abstract $reflection)
    {
        $callback = new Zend_Server_Method_Callback();
        if ($reflection instanceof Zend_Server_Reflection_Method) {
            $callback->setType($reflection->isStatic() ? 'static' : 'instance')
                     ->setClass($reflection->getDeclaringClass()->getName())
                     ->setMethod($reflection->getName());
        } elseif ($reflection instanceof Zend_Server_Reflection_Function) {
            $callback->setType('function')
                     ->setFunction($reflection->getName());
        }
        return $callback;
    }

    /**
     * Build a method signature
     *
     * @param  Zend_Server_Reflection_Function_Abstract $reflection
     * @param  null|string|object $class
     * @return Zend_Server_Method_Definition
     * @throws Zend_Server_Exception on duplicate entry
     */
    protected function _buildSignature(Zend_Server_Reflection_Function_Abstract $reflection, $class = null)
    {
        $ns         = $reflection->getNamespace();
        $name       = $reflection->getName();
        $method     = empty($ns) ? $name : $ns . '.' . $name;

        if (!$this->_overwriteExistingMethods && $this->_table->hasMethod($method)) {
            #require_once 'Zend/Server/Exception.php';
            throw new Zend_Server_Exception('Duplicate method registered: ' . $method);
        }

        $definition = new Zend_Server_Method_Definition();
        $definition->setName($method)
                   ->setCallback($this->_buildCallback($reflection))
                   ->setMethodHelp($reflection->getDescription())
                   ->setInvokeArguments($reflection->getInvokeArguments());

        foreach ($reflection->getPrototypes() as $proto) {
            $prototype = new Zend_Server_Method_Prototype();
            $prototype->setReturnType($this->_fixType($proto->getReturnType()));
            foreach ($proto->getParameters() as $parameter) {
                $param = new Zend_Server_Method_Parameter(array(
                    'type'     => $this->_fixType($parameter->getType()),
                    'name'     => $parameter->getName(),
                    'optional' => $parameter->isOptional(),
                ));
                if ($parameter->isDefaultValueAvailable()) {
                    $param->setDefaultValue($parameter->getDefaultValue());
                }
                $prototype->addParameter($param);
            }
            $definition->addPrototype($prototype);
        }
        if (is_object($class)) {
            $definition->setObject($class);
        }
        $this->_table->addMethod($definition);
        return $definition;
    }

    /**
     * Dispatch method
     *
     * @param  Zend_Server_Method_Definition $invocable
     * @param  array $params
     * @return mixed
     */
    protected function _dispatch(Zend_Server_Method_Definition $invocable, array $params)
    {
        $callback = $invocable->getCallback();
        $type     = $callback->getType();

        if ('function' == $type) {
            $function = $callback->getFunction();
            return call_user_func_array($function, $params);
        }

        $class  = $callback->getClass();
        $method = $callback->getMethod();

        if ('static' == $type) {
            return call_user_func_array(array($class, $method), $params);
        }

        $object = $invocable->getObject();
        if (!is_object($object)) {
            $invokeArgs = $invocable->getInvokeArguments();
            if (!empty($invokeArgs)) {
                $reflection = new ReflectionClass($class);
                $object     = $reflection->newInstanceArgs($invokeArgs);
            } else {
                $object = new $class;
            }
        }
        return call_user_func_array(array($object, $method), $params);
    }

    /**
     * Map PHP type to protocol type
     *
     * @param  string $type
     * @return string
     */
    abstract protected function _fixType($type);
}
