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
 * User-provided review
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Books
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Books_Extension_Review extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'gbs';
    protected $_rootElement = 'review';
    protected $_lang = null;
    protected $_type = null;

    /**
     * Constructor for Zend_Gdata_Books_Extension_Review which
     * User-provided review
     *
     * @param string|null $lang Review language.
     * @param string|null $type Type of text construct (typically text, html,
     *        or xhtml).
     * @param string|null $value Text content of the review.
     */
    public function __construct($lang = null, $type = null, $value = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Books::$namespaces);
        parent::__construct();
        $this->_lang = $lang;
        $this->_type = $type;
        $this->_text = $value;
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
        if ($this->_lang !== null) {
            $element->setAttribute('lang', $this->_lang);
        }
        if ($this->_type !== null) {
            $element->setAttribute('type', $this->_type);
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
        case 'lang':
            $this->_lang = $attribute->nodeValue;
            break;
        case 'type':
            $this->_type = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * Returns the language of link title
     *
     * @return string The lang
     */
    public function getLang()
    {
        return $this->_lang;
    }

    /**
     * Returns the type of text construct (typically 'text', 'html' or 'xhtml')
     *
     * @return string The type
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Sets the language of link title
     *
     * @param string $lang language of link title
     * @return Zend_Gdata_Books_Extension_Review Provides a fluent interface
     */
    public function setLang($lang)
    {
        $this->_lang = $lang;
        return $this;
    }

    /**
     * Sets the type of text construct (typically 'text', 'html' or 'xhtml')
     *
     * @param string $type type of text construct (typically 'text', 'html' or 'xhtml')
     * @return Zend_Gdata_Books_Extension_Review Provides a fluent interface
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }


}

