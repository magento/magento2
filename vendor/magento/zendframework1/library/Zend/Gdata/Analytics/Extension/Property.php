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
 * @package    Zend_Gdata
 * @subpackage Analytics
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Analytics
 */
class Zend_Gdata_Analytics_Extension_Property extends Zend_Gdata_Extension
{
    protected $_rootNamespace = 'ga';
    protected $_rootElement = 'property';
    protected $_value = null;
    protected $_name = null;

    /**
     * Constructs a new Zend_Gdata_Calendar_Extension_Timezone object.
     * @param string $value (optional) The text content of the element.
     */
    public function __construct($value = null, $name = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Analytics::$namespaces);
        parent::__construct();
        $this->_value = $value;
        $this->_name = $name;
    }

    /**
     * Given a DOMNode representing an attribute, tries to map the data into
     * instance members.  If no mapping is defined, the name and value are
     * stored in an array.
     *
     * @param DOMNode $attribute The DOMNode attribute needed to be handled
     */
    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
            case 'name':
                $name = explode(':', $attribute->nodeValue);
                $this->_name = end($name);
                break;
            case 'value':
                $this->_value = $attribute->nodeValue;
                break;
            default:
                parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * Get the value for this element's value attribute.
     *
     * @return string The value associated with this attribute.
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Set the value for this element's value attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Analytics_Extension_Property The element being modified.
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return Zend_Gdata_Analytics_Extension_Property
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Magic toString method allows using this directly via echo
     * Works best in PHP >= 4.2.0
     */
    public function __toString()
    {
        return $this->getValue();
    }
}
