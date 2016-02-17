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
 * @subpackage Gapps
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * @see Zend_Gdata_Gapps
 */
#require_once 'Zend/Gdata/Gapps.php';

/**
 * Represents the apps:name element used by the Apps data API. This is used
 * to represent a user's full name. This class is usually contained within
 * instances of Zend_Gdata_Gapps_UserEntry.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gapps
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Gapps_Extension_Name extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'apps';
    protected $_rootElement = 'name';

    /**
     * The associated user's family name.
     *
     * @var string
     */
    protected $_familyName = null;

    /**
     * The associated user's given name.
     *
     * @var string
     */
    protected $_givenName = null;

    /**
     * Constructs a new Zend_Gdata_Gapps_Extension_Name object.
     *
     * @param string $familyName (optional) The familyName to be set for this
     *          object.
     * @param string $givenName (optional) The givenName to be set for this
     *          object.
     */
    public function __construct($familyName = null, $givenName = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Gapps::$namespaces);
        parent::__construct();
        $this->_familyName = $familyName;
        $this->_givenName = $givenName;
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
        if ($this->_familyName !== null) {
            $element->setAttribute('familyName', $this->_familyName);
        }
        if ($this->_givenName !== null) {
            $element->setAttribute('givenName', $this->_givenName);
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
        case 'familyName':
            $this->_familyName = $attribute->nodeValue;
            break;
        case 'givenName':
            $this->_givenName = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * Get the value for this element's familyName attribute.
     *
     * @see setFamilyName
     * @return string The requested attribute.
     */
    public function getFamilyName()
    {
        return $this->_familyName;
    }

    /**
     * Set the value for this element's familyName attribute. This
     * represents a user's family name.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Gapps_Extension_Name Provides a fluent interface..
     */
    public function setFamilyName($value)
    {
        $this->_familyName = $value;
        return $this;
    }

    /**
     * Get the value for this element's givenName attribute.
     *
     * @see setGivenName
     * @return string The requested attribute.
     */
    public function getGivenName()
    {
        return $this->_givenName;
    }

    /**
     * Set the value for this element's givenName attribute. This
     * represents a user's given name.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Gapps_Extension_Name Provides a fluent interface.
     */
    public function setGivenName($value)
    {
        $this->_givenName = $value;
        return $this;
    }

    /**
     * Magic toString method allows using this directly via echo
     * Works best in PHP >= 4.2.0
     */
    public function __toString()
    {
        return $this->getGivenName() . ' ' . $this->getFamilyName();
    }

}
