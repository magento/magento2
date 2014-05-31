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
 * @version    $Id: Rating.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * Implements the gd:rating element
 *
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Extension_Rating extends Zend_Gdata_Extension
{

    protected $_rootElement = 'rating';
    protected $_min = null;
    protected $_max = null;
    protected $_numRaters = null;
    protected $_average = null;
    protected $_value = null;

    /**
     * Constructs a new Zend_Gdata_Extension_Rating object.
     *
     * @param integer $average (optional) Average rating.
     * @param integer $min (optional) Minimum rating.
     * @param integer $max (optional) Maximum rating.
     * @param integer $numRaters (optional) Number of raters.
     * @param integer $value (optional) The value of the rating.
     */
    public function __construct($average = null, $min = null,
            $max = null, $numRaters = null, $value = null)
    {
        parent::__construct();
        $this->_average = $average;
        $this->_min = $min;
        $this->_max = $max;
        $this->_numRaters = $numRaters;
        $this->_value = $value;
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for sending to the server upon updates, or
     * for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     *          child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_min !== null) {
            $element->setAttribute('min', $this->_min);
        }
        if ($this->_max !== null) {
            $element->setAttribute('max', $this->_max);
        }
        if ($this->_numRaters !== null) {
            $element->setAttribute('numRaters', $this->_numRaters);
        }
        if ($this->_average !== null) {
            $element->setAttribute('average', $this->_average);
        }
        if ($this->_value !== null) {
            $element->setAttribute('value', $this->_value);
        }

        return $element;
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
            case 'min':
                $this->_min = $attribute->nodeValue;
                break;
            case 'max':
                $this->_max = $attribute->nodeValue;
                break;
            case 'numRaters':
                $this->_numRaters = $attribute->nodeValue;
                break;
            case 'average':
                $this->_average = $attribute->nodeValue;
                break;
            case 'value':
                $this->_value = $attribute->nodeValue;
            default:
                parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * Get the value for this element's min attribute.
     *
     * @return integer The requested attribute.
     */
    public function getMin()
    {
        return $this->_min;
    }

    /**
     * Set the value for this element's min attribute.
     *
     * @param bool $value The desired value for this attribute.
     * @return Zend_Gdata_Extension_Rating The element being modified.
     */
    public function setMin($value)
    {
        $this->_min = $value;
        return $this;
    }

    /**
     * Get the value for this element's numRaters attribute.
     *
     * @return integer The requested attribute.
     */
    public function getNumRaters()
    {
        return $this->_numRaters;
    }

    /**
     * Set the value for this element's numRaters attribute.
     *
     * @param bool $value The desired value for this attribute.
     * @return Zend_Gdata_Extension_Rating The element being modified.
     */
    public function setNumRaters($value)
    {
        $this->_numRaters = $value;
        return $this;
    }

    /**
     * Get the value for this element's average attribute.
     *
     * @return integer The requested attribute.
     */
    public function getAverage()
    {
        return $this->_average;
    }

    /**
     * Set the value for this element's average attribute.
     *
     * @param bool $value The desired value for this attribute.
     * @return Zend_Gdata_Extension_Rating The element being modified.
     */
    public function setAverage($value)
    {
        $this->_average = $value;
        return $this;
    }

    /**
     * Get the value for this element's max attribute.
     *
     * @return integer The requested attribute.
     */
    public function getMax()
    {
        return $this->_max;
    }

    /**
     * Set the value for this element's max attribute.
     *
     * @param bool $value The desired value for this attribute.
     * @return Zend_Gdata_Extension_Rating The element being modified.
     */
    public function setMax($value)
    {
        $this->_max = $value;
        return $this;
    }

    /**
     * Get the value for this element's value attribute.
     *
     * @return integer The requested attribute.
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Set the value for this element's value attribute.
     *
     * @param bool $value The desired value for this attribute.
     * @return Zend_Gdata_Extension_Rating The element being modified.
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

}
