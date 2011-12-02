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
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ExtendedProperty.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * Data model for gd:extendedProperty element, used by some Gdata
 * services to implement arbitrary name/value pair storage
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Extension_ExtendedProperty extends Zend_Gdata_Extension
{

    protected $_rootElement = 'extendedProperty';
    protected $_name = null;
    protected $_value = null;

    public function __construct($name = null, $value = null)
    {
        parent::__construct();
        $this->_name = $name;
        $this->_value = $value;
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_name !== null) {
            $element->setAttribute('name', $this->_name);
        }
        if ($this->_value !== null) {
            $element->setAttribute('value', $this->_value);
        }
        return $element;
    }

    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'name':
            $this->_name = $attribute->nodeValue;
            break;
        case 'value':
            $this->_value = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    public function __toString()
    {
        return $this->getName() . '=' . $this->getValue();
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($value)
    {
        $this->_name = $value;
        return $this;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

}
