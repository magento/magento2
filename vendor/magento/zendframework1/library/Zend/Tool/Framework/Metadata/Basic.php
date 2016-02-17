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
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Tool_Framework_Metadata_Interface
 */
#require_once 'Zend/Tool/Framework/Metadata/Interface.php';

/**
 * @see Zend_Tool_Framework_Metadata_Attributable
 */
#require_once 'Zend/Tool/Framework/Metadata/Attributable.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_Metadata_Basic
    implements Zend_Tool_Framework_Metadata_Interface, Zend_Tool_Framework_Metadata_Attributable
{

    /**#@+
     * Search constants
     */
    const ATTRIBUTES_ALL        = 'attributesAll';
    const ATTRIBUTES_NO_PARENT  = 'attributesParent';
    /**#@-*/

    /**#@+
     * @var string
     */
    protected $_type        = 'Basic';
    protected $_name        = null;
    protected $_value       = null;
    /**#@-*/

    /**
     * @var mixed
     */
    protected $_reference   = null;

    /**
     * Constructor - allows for the setting of options
     *
     * @param array $options
     */
    public function __construct(Array $options = array())
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * setOptions() - standard issue implementation, this will set any
     * options that are supported via a set method.
     *
     * @param array $options
     * @return Zend_Tool_Framework_Metadata_Basic
     */
    public function setOptions(Array $options)
    {
        foreach ($options as $optionName => $optionValue) {
            $setMethod = 'set' . $optionName;
            if (method_exists($this, $setMethod)) {
                $this->{$setMethod}($optionValue);
            }
        }

        return $this;
    }

    /**
     * getType()
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * setType()
     *
     * @param string $type
     * @return Zend_Tool_Framework_Metadata_Basic
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * setName()
     *
     * @param string $name
     * @return Zend_Tool_Framework_Metadata_Basic
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * getValue()
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * setValue()
     *
     * @param unknown_type $Value
     * @return Zend_Tool_Framework_Metadata_Basic
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    /**
     * setReference()
     *
     * @param mixed $reference
     * @return Zend_Tool_Framework_Metadata_Basic
     */
    public function setReference($reference)
    {
        $this->_reference = $reference;
        return $this;
    }

    /**
     * getReference()
     *
     * @return mixed
     */
    public function getReference()
    {
        return $this->_reference;
    }

    /**
     * getAttributes() - this will retrieve any attributes of this object that exist as properties
     * This is most useful for printing metadata.
     *
     * @param const $type
     * @return array
     */
    public function getAttributes($type = self::ATTRIBUTES_ALL, $stringRepresentationOfNonScalars = false)
    {
        $thisReflection = new ReflectionObject($this);

        $metadataPairValues = array();

        foreach (get_object_vars($this) as $varName => $varValue) {
            if ($type == self::ATTRIBUTES_NO_PARENT && ($thisReflection->getProperty($varName)->getDeclaringClass()->getName() == 'Zend_Tool_Framework_Metadata_Basic')) {
                continue;
            }

            if ($stringRepresentationOfNonScalars) {

                if (is_object($varValue)) {
                    $varValue = '(object)';
                }

                if ($varValue === null) {
                    $varValue = '(null)';
                }

            }

            $metadataPairValues[ltrim($varName, '_')] = $varValue;
        }

        return $metadataPairValues;
    }

    /**
     * __toString() - string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return 'Type: ' . $this->_type . ', Name: ' . $this->_name . ', Value: ' . (is_array($this->_value) ? http_build_query($this->_value) : (string) $this->_value);
    }
}
