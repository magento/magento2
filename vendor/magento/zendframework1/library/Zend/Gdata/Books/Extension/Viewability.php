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
 * @subpackage Books
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * Describes a viewability
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Books
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Books_Extension_Viewability extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'gbs';
    protected $_rootElement = 'viewability';
    protected $_value = null;

    /**
     * Constructor for Zend_Gdata_Books_Extension_Viewability which
     * Describes a viewability
     *
     * @param string|null $value A programmatic value representing the book's
     *        viewability mode.
     */
    public function __construct($value = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Books::$namespaces);
        parent::__construct();
        $this->_value = $value;
    }

    /**
     * Retrieves DOMElement which corresponds to this element and all
     * child properties. This is used to build this object back into a DOM
     * and eventually XML text for sending to the server upon updates, or
     * for application storage/persistance.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     * child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc);
        if ($this->_value !== null) {
            $element->setAttribute('value', $this->_value);
        }
        return $element;
    }

    /**
     * Extracts XML attributes from the DOM and converts them to the
     * appropriate object members.
     *
     * @param DOMNode $attribute The DOMNode attribute to be handled.
     */
    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'value':
            $this->_value = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * Returns the programmatic value that describes the viewability of a volume
     * in Google Book Search
     *
     * @return string The value
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Sets the programmatic value that describes the viewability of a volume in
     * Google Book Search
     *
     * @param string $value programmatic value that describes the viewability
     *     of a volume in Googl eBook Search
     * @return Zend_Gdata_Books_Extension_Viewability Provides a fluent
     *     interface
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }


}

