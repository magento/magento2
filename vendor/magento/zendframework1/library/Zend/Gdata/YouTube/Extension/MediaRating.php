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
 * @subpackage Media
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * Represents the media:rating element specific to YouTube.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_YouTube_Extension_MediaRating extends Zend_Gdata_Extension
{

    protected $_rootElement = 'rating';
    protected $_rootNamespace = 'media';

    /**
     * @var string
     */
    protected $_scheme = null;

    /**
     * @var string
     */
    protected $_country = null;

    /**
     * Constructs a new MediaRating element
     *
     * @param string $text
     * @param string $scheme
     * @param string $country
     */
    public function __construct($text = null, $scheme = null, $country = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct();
        $this->_scheme = $scheme;
        $this->_country = $country;
        $this->_text = $text;
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for sending to the server upon updates, or
     * for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     *         child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_scheme !== null) {
            $element->setAttribute('scheme', $this->_scheme);
        }
        if ($this->_country != null) {
            $element->setAttribute('country', $this->_country);
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
        case 'scheme':
            $this->_scheme = $attribute->nodeValue;
            break;
        case 'country':
            $this->_country = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->_scheme;
    }

    /**
     * @param string $value
     * @return Zend_Gdata_YouTube_Extension_MediaRating Provides a fluent interface
     */
    public function setScheme($value)
    {
        $this->_scheme = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->_country;
    }

    /**
     * @param string $value
     * @return Zend_Gdata_YouTube_Extension_MediaRating Provides a fluent interface
     */
    public function setCountry($value)
    {
        $this->_country = $value;
        return $this;
    }


}
