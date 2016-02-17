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
 * @see Zend_Gdata_App_Extension
 */
#require_once 'Zend/Gdata/App/Extension.php';

/**
 * Represents the media:text element
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Media
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Media_Extension_MediaText extends Zend_Gdata_Extension
{

    protected $_rootElement = 'text';
    protected $_rootNamespace = 'media';

    /**
     * @var string
     */
    protected $_type = null;

    /**
     * @var string
     */
    protected $_lang = null;

    /**
     * @var string
     */
    protected $_start = null;

    /**
     * @var string
     */
    protected $_end = null;

    /**
     * Constructs a new MediaText element
     *
     * @param string $text
     * @param string $type
     * @param string $lang
     * @param string $start
     * @param string $end
     */
    public function __construct($text = null, $type = null, $lang = null,
            $start = null, $end = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct();
        $this->_text = $text;
        $this->_type = $type;
        $this->_lang = $lang;
        $this->_start = $start;
        $this->_end = $end;
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
        if ($this->_type !== null) {
            $element->setAttribute('type', $this->_type);
        }
        if ($this->_lang !== null) {
            $element->setAttribute('lang', $this->_lang);
        }
        if ($this->_start !== null) {
            $element->setAttribute('start', $this->_start);
        }
        if ($this->_end !== null) {
            $element->setAttribute('end', $this->_end);
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
        case 'type':
            $this->_type = $attribute->nodeValue;
            break;
        case 'lang':
            $this->_lang = $attribute->nodeValue;
            break;
        case 'start':
            $this->_start = $attribute->nodeValue;
            break;
        case 'end':
            $this->_end = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @param string $value
     * @return Zend_Gdata_Media_Extension_MediaText Provides a fluent interface
     */
    public function setType($value)
    {
        $this->_type = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->_lang;
    }

    /**
     * @param string $value
     * @return Zend_Gdata_Media_Extension_MediaText Provides a fluent interface
     */
    public function setLang($value)
    {
        $this->_lang = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getStart()
    {
        return $this->_start;
    }

    /**
     * @param string $value
     * @return Zend_Gdata_Media_Extension_MediaText Provides a fluent interface
     */
    public function setStart($value)
    {
        $this->_start = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getEnd()
    {
        return $this->_end;
    }

    /**
     * @param string $value
     * @return Zend_Gdata_Media_Extension_MediaText Provides a fluent interface
     */
    public function setEnd($value)
    {
        $this->_end = $value;
        return $this;
    }
}
