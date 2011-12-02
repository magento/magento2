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
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: State.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * Represents the yt:state element used by the YouTube data API
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_YouTube_Extension_State extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'yt';
    protected $_rootElement = 'state';
    protected $_name = null;
    protected $_reasonCode = null;
    protected $_helpUrl = null;

    /**
     * Constructs a new Zend_Gdata_YouTube_Extension_State object.
     *
     * @param string $explanation(optional) The explanation of this state
     * @param string $name(optional) The name value
     * @param string $reasonCode(optional) The reasonCode value
     * @param string $helpUrl(optional) The helpUrl value
     */
    public function __construct($explanation = null, $name = null,
                                $reasonCode = null, $helpUrl = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct();
        $this->_text = $explanation;
        $this->_name = $name;
        $this->_reasonCode = $reasonCode;
        $this->_helpUrl = $reasonCode;
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
        if ($this->_name !== null) {
            $element->setAttribute('name', $this->_name);
        }
        if ($this->_reasonCode !== null) {
            $element->setAttribute('reasonCode', $this->_reasonCode);
        }
        if ($this->_helpUrl !== null) {
            $element->setAttribute('helpUrl', $this->_helpUrl);
        }
        return $element;
    }

    /**
     * Given a DOMNode representing an attribute, tries to map the data into
     * instance members.  If no mapping is defined, the name and valueare
     * stored in an array.
     * TODO: Convert attributes to proper types
     *
     * @param DOMNode $attribute The DOMNode attribute needed to be handled
     */
    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'name':
            $this->_name = $attribute->nodeValue;
            break;
        case 'reasonCode':
            $this->_reasonCode = $attribute->nodeValue;
            break;
        case 'helpUrl':
            $this->_helpUrl = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * Get the value for this element's name attribute.
     *
     * @return int The value associated with this attribute.
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set the value for this element's name attribute.
     *
     * @param int $value The desired value for this attribute.
     * @return Zend_Gdata_YouTube_Extension_State The element being modified.
     */
    public function setName($value)
    {
        $this->_name = $value;
        return $this;
    }

    /**
     * Get the value for this element's reasonCode attribute.
     *
     * @return int The value associated with this attribute.
     */
    public function getReasonCode()
    {
        return $this->_reasonCode;
    }

    /**
     * Set the value for this element's reasonCode attribute.
     *
     * @param int $value The desired value for this attribute.
     * @return Zend_Gdata_YouTube_Extension_State The element being modified.
     */
    public function setReasonCode($value)
    {
        $this->_reasonCode = $value;
        return $this;
    }

    /**
     * Get the value for this element's helpUrl attribute.
     *
     * @return int The value associated with this attribute.
     */
    public function getHelpUrl()
    {
        return $this->_helpUrl;
    }

    /**
     * Set the value for this element's helpUrl attribute.
     *
     * @param int $value The desired value for this attribute.
     * @return Zend_Gdata_YouTube_Extension_State The element being modified.
     */
    public function setHelpUrl($value)
    {
        $this->_helpUrl = $value;
        return $this;
    }

    /**
     * Magic toString method allows using this directly via echo
     * Works best in PHP >= 4.2.0
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_text;
    }

}
