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
 * @package    Zend_Service
 * @subpackage StrikeIron
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Decorator.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Decorates a StrikeIron response object returned by the SOAP extension
 * to provide more a PHP-like interface.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage StrikeIron
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_StrikeIron_Decorator
{
    /**
     * Name of the decorated object
     * @var null|string
     */
    protected $_name = null;

    /**
     * Object to decorate
     * @var object
     */
    protected $_object = null;

    /**
     * Class constructor
     *
     * @param object       $object  Object to decorate
     * @param null|string  $name    Name of the object
     */
    public function __construct($object, $name = null)
    {
        $this->_object = $object;
        $this->_name   = $name;
    }

    /**
     * Proxy property access to the decorated object, inflecting
     * the property name and decorating any child objects returned.
     * If the property is not found in the decorated object, return
     * NULL as a convenience feature to avoid notices.
     *
     * @param  string $property  Property name to retrieve
     * @return mixed             Value of property or NULL
     */
    public function __get($property)
    {
        $result = null;

        if (! isset($this->_object->$property)) {
            $property = $this->_inflect($property);
        }

        if (isset($this->_object->$property)) {
            $result = $this->_object->$property;
            $result = $this->_decorate($result);
        }
        return $result;
    }

    /**
     * Proxy method calls to the decorated object.  This will only
     * be used when the SOAPClient returns a custom PHP object via
     * its classmap option so no inflection is done.
     *
     * @param string  $method  Name of method called
     * @param array   $args    Arguments for method
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->_object, $method), $args);
    }

    /**
     * Inflect a property name from PHP-style to the result object's
     * style.  The default implementation here only inflects the case
     * of the first letter, e.g. from "fooBar" to "FooBar".
     *
     * @param  string $property  Property name to inflect
     * @return string            Inflected property name
     */
    protected function _inflect($property)
    {
        return ucfirst($property);
    }

    /**
     * Decorate a value returned by the result object.  The default
     * implementation here only decorates child objects.
     *
     * @param  mixed  $result  Value to decorate
     * @return mixed           Decorated result
     */
    protected function _decorate($result)
    {
        if (is_object($result)) {
            $result = new self($result);
        }
        return $result;
    }

    /**
     * Return the object being decorated
     *
     * @return object
     */
    public function getDecoratedObject()
    {
        return $this->_object;
    }

    /**
     * Return the name of the object being decorated
     *
     * @return null|string
     */
    public function getDecoratedObjectName()
    {
        return $this->_name;
    }
}
