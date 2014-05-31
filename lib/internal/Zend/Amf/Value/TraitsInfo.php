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
 * @package    Zend_Amf
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: TraitsInfo.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Zend_Amf_Value_TraitsInfo
 *
 * @package    Zend_Amf
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Value_TraitsInfo
{
    /**
     * @var string Class name
     */
    protected $_className;

    /**
     * @var bool Whether or not this is a dynamic class
     */
    protected $_dynamic;

    /**
     * @var bool Whether or not the class is externalizable
     */
    protected $_externalizable;

    /**
     * @var array Class properties
     */
    protected $_properties;

    /**
     * Used to keep track of all class traits of an AMF3 object
     *
     * @param  string $className
     * @param  boolean $dynamic
     * @param  boolean $externalizable
     * @param  boolean $properties
     * @return void
     */
    public function __construct($className, $dynamic=false, $externalizable=false, $properties=null)
    {
        $this->_className      = $className;
        $this->_dynamic        = $dynamic;
        $this->_externalizable = $externalizable;
        $this->_properties     = $properties;
    }

    /**
     * Test if the class is a dynamic class
     *
     * @return boolean
     */
    public function isDynamic()
    {
        return $this->_dynamic;
    }

    /**
     * Test if class is externalizable
     *
     * @return boolean
     */
    public function isExternalizable()
    {
        return $this->_externalizable;
    }

    /**
     * Return the number of properties in the class
     *
     * @return int
     */
    public function length()
    {
        return count($this->_properties);
    }

    /**
     * Return the class name
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->_className;
    }

    /**
     * Add an additional property
     *
     * @param  string $name
     * @return Zend_Amf_Value_TraitsInfo
     */
    public function addProperty($name)
    {
        $this->_properties[] = $name;
        return $this;
    }

    /**
     * Add all properties of the class.
     *
     * @param  array $props
     * @return Zend_Amf_Value_TraitsInfo
     */
    public function addAllProperties(array $props)
    {
        $this->_properties = $props;
        return $this;
    }

    /**
     * Get the property at a given index
     *
     * @param  int $index
     * @return string
     */
    public function getProperty($index)
    {
        return $this->_properties[(int) $index];
    }

    /**
     * Return all properties of the class.
     *
     * @return array
     */
    public function getAllProperties()
    {
        return $this->_properties;
    }
}
