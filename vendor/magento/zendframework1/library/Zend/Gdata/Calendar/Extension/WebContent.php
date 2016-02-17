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
 * @subpackage Calendar
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * Represents the gCal:webContent element used by the Calendar data API
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Calendar
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Calendar_Extension_WebContent extends Zend_Gdata_App_Extension
{

    protected $_rootNamespace = 'gCal';
    protected $_rootElement = 'webContent';
    protected $_url = null;
    protected $_height = null;
    protected $_width = null;

    /**
     * Constructs a new Zend_Gdata_Calendar_Extension_WebContent object.
     * @param string $url (optional) The value for this element's URL attribute.
     * @param string $height (optional) The value for this element's height attribute.
     * @param string $width (optional) The value for this element's width attribute.
     */
    public function __construct($url = null, $height = null, $width = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Calendar::$namespaces);
        parent::__construct();
        $this->_url = $url;
        $this->_height = $height;
        $this->_width = $width;
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for sending to the server upon updates, or
     * for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     * child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->url != null) {
            $element->setAttribute('url', $this->_url);
        }
        if ($this->height != null) {
            $element->setAttribute('height', $this->_height);
        }
        if ($this->width != null) {
            $element->setAttribute('width', $this->_width);
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
                case 'url':
                        $this->_url = $attribute->nodeValue;
                        break;
                case 'height':
                        $this->_height = $attribute->nodeValue;
                        break;
                case 'width':
                        $this->_width = $attribute->nodeValue;
                        break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * Get the value for this element's URL attribute.
     *
     * @return string The desired value for this attribute.
     */
    public function getURL()
    {
        return $this->_url;
    }

    /**
     * Set the value for this element's URL attribute.
     *
     * @param bool $value The desired value for this attribute.
     * @return Zend_Gdata_Calendar_Extension_WebContent The element being modified.
     */
    public function setURL($value)
    {
        $this->_url = $value;
        return $this;
    }

    /**
     * Get the value for this element's height attribute.
     *
     * @return int The desired value for this attribute.
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * Set the value for this element's height attribute.
     *
     * @param int $value The desired value for this attribute.
     * @return Zend_Gdata_Calendar_Extension_WebContent The element being modified.
     */
    public function setHeight($value)
    {
        $this->_height = $value;
        return $this;
    }

    /**
     * Get the value for this element's height attribute.
     *
     * @return int The desired value for this attribute.
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * Set the value for this element's height attribute.
     *
     * @param int $value The desired value for this attribute.
     * @return Zend_Gdata_Calendar_Extension_WebContent The element being modified.
     */
    public function setWidth($value)
    {
        $this->_width = $value;
        return $this;
    }

}
