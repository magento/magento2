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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: MediaPlayer.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_App_Extension
 */
#require_once 'Zend/Gdata/App/Extension.php';

/**
 * Represents the media:player element
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Media
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Media_Extension_MediaPlayer extends Zend_Gdata_Extension
{

    protected $_rootElement = 'player';
    protected $_rootNamespace = 'media';

    /**
     * @var string
     */
    protected $_url = null;

    /**
     * @var int
     */
    protected $_width = null;

    /**
     * @var int
     */
    protected $_height = null;

    /**
     * Constructs a new MediaPlayer element
     *
     * @param string $url
     * @param int $width
     * @param int $height
     */
    public function __construct($url = null, $width = null, $height = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct();
        $this->_url = $url;
        $this->_width = $width;
        $this->_height = $height;
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
        if ($this->_url !== null) {
            $element->setAttribute('url', $this->_url);
        }
        if ($this->_width !== null) {
            $element->setAttribute('width', $this->_width);
        }
        if ($this->_height !== null) {
            $element->setAttribute('height', $this->_height);
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
        case 'width':
            $this->_width = $attribute->nodeValue;
            break;
        case 'height':
            $this->_height = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * @param string $value
     * @return Zend_Gdata_Media_Extension_MediaPlayer Provides a fluent interface
     */
    public function setUrl($value)
    {
        $this->_url = $value;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * @param int $value
     * @return Zend_Gdata_Media_Extension_MediaPlayer Provides a fluent interface
     */
    public function setWidth($value)
    {
        $this->_width = $value;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * @param int $value
     * @return Zend_Gdata_Media_Extension_MediaPlayer Provides a fluent interface
     */
    public function setHeight($value)
    {
        $this->_height = $value;
        return $this;
    }

}
