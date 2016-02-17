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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * Represents the yt:statistics element used by the YouTube data API
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_YouTube_Extension_Statistics extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'yt';
    protected $_rootElement = 'statistics';

    /**
     * The videoWatchCount attribute specifies the number of videos
     * that a user has watched on YouTube. The videoWatchCount attribute
     * is only specified when the <yt:statistics> tag appears within a
     * user profile entry.
     *
     * @var integer
     */
    protected $_videoWatchCount = null;

    /**
     * When the viewCount attribute refers to a video entry, the attribute
     * specifies the number of times that the video has been viewed.
     * When the viewCount attribute refers to a user profile, the attribute
     * specifies the number of times that the user's profile has been
     * viewed.
     *
     * @var integer
     */
    protected $_viewCount = null;

    /**
     * The subscriberCount attribute specifies the number of YouTube users
     * who have subscribed to a particular user's YouTube channel.
     * The subscriberCount attribute is only specified when the
     * <yt:statistics> tag appears within a user profile entry.
     *
     * @var integer
     */
    protected $_subscriberCount = null;

    /**
     * The lastWebAccess attribute indicates the most recent time that
     * a particular user used YouTube.
     *
     * @var string
     */
    protected $_lastWebAccess = null;

    /**
     * The favoriteCount attribute specifies the number of YouTube users
     * who have added a video to their list of favorite videos. The
     * favoriteCount attribute is only specified when the
     * <yt:statistics> tag appears within a video entry.
     *
     * @var integer
     */
    protected $_favoriteCount = null;

    /**
     * Constructs a new Zend_Gdata_YouTube_Extension_Statistics object.
     * @param string $viewCount(optional) The viewCount value
     * @param string $videoWatchCount(optional) The videoWatchCount value
     * @param string $subscriberCount(optional) The subscriberCount value
     * @param string $lastWebAccess(optional) The lastWebAccess value
     * @param string $favoriteCount(optional) The favoriteCount value
     */
    public function __construct($viewCount = null, $videoWatchCount = null,
        $subscriberCount = null, $lastWebAccess = null,
        $favoriteCount = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct();
        $this->_viewCount = $viewCount;
        $this->_videoWatchCount = $videoWatchCount;
        $this->_subscriberCount = $subscriberCount;
        $this->_lastWebAccess = $lastWebAccess;
        $this->_favoriteCount = $favoriteCount;
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
        if ($this->_videoWatchCount !== null) {
            $element->setAttribute('watchCount', $this->_videoWatchCount);
        }
        if ($this->_viewCount !== null) {
            $element->setAttribute('viewCount', $this->_viewCount);
        }
        if ($this->_subscriberCount !== null) {
            $element->setAttribute('subscriberCount',
                $this->_subscriberCount);
        }
        if ($this->_lastWebAccess !== null) {
            $element->setAttribute('lastWebAccess',
                $this->_lastWebAccess);
        }
        if ($this->_favoriteCount !== null) {
            $element->setAttribute('favoriteCount',
                $this->_favoriteCount);
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
        case 'videoWatchCount':
            $this->_videoWatchCount = $attribute->nodeValue;
            break;
        case 'viewCount':
            $this->_viewCount = $attribute->nodeValue;
            break;
        case 'subscriberCount':
            $this->_subscriberCount = $attribute->nodeValue;
            break;
        case 'lastWebAccess':
            $this->_lastWebAccess = $attribute->nodeValue;
            break;
        case 'favoriteCount':
            $this->_favoriteCount = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * Get the value for this element's viewCount attribute.
     *
     * @return int The value associated with this attribute.
     */
    public function getViewCount()
    {
        return $this->_viewCount;
    }

    /**
     * Set the value for this element's viewCount attribute.
     *
     * @param int $value The desired value for this attribute.
     * @return Zend_Gdata_YouTube_Extension_Statistics The element being
     * modified.
     */
    public function setViewCount($value)
    {
        $this->_viewCount = $value;
        return $this;
    }

    /**
     * Get the value for this element's videoWatchCount attribute.
     *
     * @return int The value associated with this attribute.
     */
    public function getVideoWatchCount()
    {
        return $this->_videoWatchCount;
    }

    /**
     * Set the value for this element's videoWatchCount attribute.
     *
     * @param int $value The desired value for this attribute.
     * @return Zend_Gdata_YouTube_Extension_Statistics The element being
     * modified.
     */
    public function setVideoWatchCount($value)
    {
        $this->_videoWatchCount = $value;
        return $this;
    }

    /**
     * Get the value for this element's subscriberCount attribute.
     *
     * @return int The value associated with this attribute.
     */
    public function getSubscriberCount()
    {
        return $this->_subscriberCount;
    }

    /**
     * Set the value for this element's subscriberCount attribute.
     *
     * @param int $value The desired value for this attribute.
     * @return Zend_Gdata_YouTube_Extension_Statistics The element being
     * modified.
     */
    public function setSubscriberCount($value)
    {
        $this->_subscriberCount = $value;
        return $this;
    }

    /**
     * Get the value for this element's lastWebAccess attribute.
     *
     * @return int The value associated with this attribute.
     */
    public function getLastWebAccess()
    {
        return $this->_lastWebAccess;
    }

    /**
     * Set the value for this element's lastWebAccess attribute.
     *
     * @param int $value The desired value for this attribute.
     * @return Zend_Gdata_YouTube_Extension_Statistics The element being
     * modified.
     */
    public function setLastWebAccess($value)
    {
        $this->_lastWebAccess = $value;
        return $this;
    }

    /**
     * Get the value for this element's favoriteCount attribute.
     *
     * @return int The value associated with this attribute.
     */
    public function getFavoriteCount()
    {
        return $this->_favoriteCount;
    }

    /**
     * Set the value for this element's favoriteCount attribute.
     *
     * @param int $value The desired value for this attribute.
     * @return Zend_Gdata_YouTube_Extension_Statistics The element being
     * modified.
     */
    public function setFavoriteCount($value)
    {
        $this->_favoriteCount = $value;
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
        return 'View Count=' . $this->_viewCount .
            ' VideoWatchCount=' . $this->_videoWatchCount .
            ' SubscriberCount=' . $this->_subscriberCount .
            ' LastWebAccess=' . $this->_lastWebAccess .
            ' FavoriteCount=' . $this->_favoriteCount;
    }

}
