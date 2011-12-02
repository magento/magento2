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
 * @version    $Id: MediaContent.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * Represents the media:content element of Media RSS.
 * Represents media objects.  Multiple media objects representing
 * the same content can be represented using a
 * media:group (Zend_Gdata_Media_Extension_MediaGroup) element.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Media
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Media_Extension_MediaContent extends Zend_Gdata_Extension
{
    protected $_rootElement = 'content';
    protected $_rootNamespace = 'media';

    /**
     * @var string
     */
    protected $_url = null;

    /**
     * @var int
     */
    protected $_fileSize = null;

    /**
     * @var string
     */
    protected $_type = null;

    /**
     * @var string
     */
    protected $_medium = null;

    /**
     * @var string
     */
    protected $_isDefault = null;

    /**
     * @var string
     */
    protected $_expression = null;

    /**
     * @var int
     */
    protected $_bitrate = null;

    /**
     * @var int
     */
    protected $_framerate = null;

    /**
     * @var int
     */
    protected $_samplingrate = null;

    /**
     * @var int
     */
    protected $_channels = null;

    /**
     * @var int
     */
    protected $_duration = null;

    /**
     * @var int
     */
    protected $_height = null;

    /**
     * @var int
     */
    protected $_width = null;

    /**
     * @var string
     */
    protected $_lang = null;

    /**
     * Creates an individual MediaContent object.
     */
    public function __construct($url = null, $fileSize = null, $type = null,
            $medium = null, $isDefault = null, $expression = null,
            $bitrate = null, $framerate = null, $samplingrate = null,
            $channels = null, $duration = null, $height = null, $width = null,
            $lang = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct();
        $this->_url = $url;
        $this->_fileSize = $fileSize;
        $this->_type = $type;
        $this->_medium = $medium;
        $this->_isDefault = $isDefault;
        $this->_expression = $expression;
        $this->_bitrate = $bitrate;
        $this->_framerate = $framerate;
        $this->_samplingrate = $samplingrate;
        $this->_channels = $channels;
        $this->_duration = $duration;
        $this->_height = $height;
        $this->_width = $width;
        $this->_lang = $lang;
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
        if ($this->_fileSize !== null) {
            $element->setAttribute('fileSize', $this->_fileSize);
        }
        if ($this->_type !== null) {
            $element->setAttribute('type', $this->_type);
        }
        if ($this->_medium !== null) {
            $element->setAttribute('medium', $this->_medium);
        }
        if ($this->_isDefault !== null) {
            $element->setAttribute('isDefault', $this->_isDefault);
        }
        if ($this->_expression !== null) {
            $element->setAttribute('expression', $this->_expression);
        }
        if ($this->_bitrate !== null) {
            $element->setAttribute('bitrate', $this->_bitrate);
        }
        if ($this->_framerate !== null) {
            $element->setAttribute('framerate', $this->_framerate);
        }
        if ($this->_samplingrate !== null) {
            $element->setAttribute('samplingrate', $this->_samplingrate);
        }
        if ($this->_channels !== null) {
            $element->setAttribute('channels', $this->_channels);
        }
        if ($this->_duration !== null) {
            $element->setAttribute('duration', $this->_duration);
        }
        if ($this->_height !== null) {
            $element->setAttribute('height', $this->_height);
        }
        if ($this->_width !== null) {
            $element->setAttribute('width', $this->_width);
        }
        if ($this->_lang !== null) {
            $element->setAttribute('lang', $this->_lang);
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
            case 'fileSize':
                $this->_fileSize = $attribute->nodeValue;
                break;
            case 'type':
                $this->_type = $attribute->nodeValue;
                break;
            case 'medium':
                $this->_medium = $attribute->nodeValue;
                break;
            case 'isDefault':
                $this->_isDefault = $attribute->nodeValue;
                break;
            case 'expression':
                $this->_expression = $attribute->nodeValue;
                break;
            case 'bitrate':
                $this->_bitrate = $attribute->nodeValue;
                break;
            case 'framerate':
                $this->_framerate = $attribute->nodeValue;
                break;
            case 'samplingrate':
                $this->_samplingrate = $attribute->nodeValue;
                break;
            case 'channels':
                $this->_channels = $attribute->nodeValue;
                break;
            case 'duration':
                $this->_duration = $attribute->nodeValue;
                break;
            case 'height':
                $this->_height = $attribute->nodeValue;
                break;
            case 'width':
                $this->_width = $attribute->nodeValue;
                break;
            case 'lang':
                $this->_lang = $attribute->nodeValue;
                break;
            default:
                parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * Returns the URL representing this MediaContent object
     *
     * @return string   The URL representing this MediaContent object.
     */
    public function __toString()
    {
        return $this->getUrl();
    }

    /**
     * @return string   The direct URL to the media object
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * @param string $value     The direct URL to the media object
     * @return Zend_Gdata_Media_Extension_MediaContent  Provides a fluent interface
     */
    public function setUrl($value)
    {
        $this->_url = $value;
        return $this;
    }

    /**
     * @return int  The size of the media in bytes
     */
    public function getFileSize()
    {
        return $this->_fileSize;
    }

    /**
     * @param int $value
     * @return Zend_Gdata_Media_Extension_MediaContent  Provides a fluent interface
     */
    public function setFileSize($value)
    {
        $this->_fileSize = $value;
        return $this;
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
     * @return Zend_Gdata_Media_Extension_MediaContent  Provides a fluent interface
     */
    public function setType($value)
    {
        $this->_type = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getMedium()
    {
        return $this->_medium;
    }

    /**
     * @param string $value
     * @return Zend_Gdata_Media_Extension_MediaContent  Provides a fluent interface
     */
    public function setMedium($value)
    {
        $this->_medium = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->_isDefault;
    }

    /**
     * @param bool $value
     * @return Zend_Gdata_Media_Extension_MediaContent  Provides a fluent interface
     */
    public function setIsDefault($value)
    {
        $this->_isDefault = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->_expression;
    }

    /**
     * @param string
     * @return Zend_Gdata_Media_Extension_MediaContent  Provides a fluent interface
     */
    public function setExpression($value)
    {
        $this->_expression = $value;
        return $this;
    }

    /**
     * @return int
     */
    public function getBitrate()
    {
        return $this->_bitrate;
    }

    /**
     * @param int
     * @return Zend_Gdata_Media_Extension_MediaContent  Provides a fluent interface
     */
    public function setBitrate($value)
    {
        $this->_bitrate = $value;
        return $this;
    }

    /**
     * @return int
     */
    public function getFramerate()
    {
        return $this->_framerate;
    }

    /**
     * @param int
     * @return Zend_Gdata_Media_Extension_MediaContent  Provides a fluent interface
     */
    public function setFramerate($value)
    {
        $this->_framerate = $value;
        return $this;
    }

    /**
     * @return int
     */
    public function getSamplingrate()
    {
        return $this->_samplingrate;
    }

    /**
     * @param int
     * @return Zend_Gdata_Media_Extension_MediaContent  Provides a fluent interface
     */
    public function setSamplingrate($value)
    {
        $this->_samplingrate = $value;
        return $this;
    }

    /**
     * @return int
     */
    public function getChannels()
    {
        return $this->_channels;
    }

    /**
     * @param int
     * @return Zend_Gdata_Media_Extension_MediaContent  Provides a fluent interface
     */
    public function setChannels($value)
    {
        $this->_channels = $value;
        return $this;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->_duration;
    }

    /**
     *
     * @param int
     * @return Zend_Gdata_Media_Extension_MediaContent  Provides a fluent interface
     */
    public function setDuration($value)
    {
        $this->_duration = $value;
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
     * @param int
     * @return Zend_Gdata_Media_Extension_MediaContent  Provides a fluent interface
     */
    public function setHeight($value)
    {
        $this->_height = $value;
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
     * @param int
     * @return Zend_Gdata_Media_Extension_MediaContent  Provides a fluent interface
     */
    public function setWidth($value)
    {
        $this->_width = $value;
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
     * @param string
     * @return Zend_Gdata_Media_Extension_MediaContent  Provides a fluent interface
     */
    public function setLang($value)
    {
        $this->_lang = $value;
        return $this;
    }

}
