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
 * @version    $Id: MediaGroup.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * @see Zend_Gdata_Entry
 */
#require_once 'Zend/Gdata/Entry.php';

/**
 * @see Zend_Gdata_Media_Extension_MediaContent
 */
#require_once 'Zend/Gdata/Media/Extension/MediaContent.php';

/**
 * @see Zend_Gdata_Media_Extension_MediaCategory
 */
#require_once 'Zend/Gdata/Media/Extension/MediaCategory.php';

/**
 * @see Zend_Gdata_Media_Extension_MediaCopyright
 */
#require_once 'Zend/Gdata/Media/Extension/MediaCopyright.php';

/**
 * @see Zend_Gdata_Media_Extension_MediaCredit
 */
#require_once 'Zend/Gdata/Media/Extension/MediaCredit.php';

/**
 * @see Zend_Gdata_Media_Extension_MediaDescription
 */
#require_once 'Zend/Gdata/Media/Extension/MediaDescription.php';

/**
 * @see Zend_Gdata_Media_Extension_MediaHash
 */
#require_once 'Zend/Gdata/Media/Extension/MediaHash.php';

/**
 * @see Zend_Gdata_Media_Extension_MediaKeywords
 */
#require_once 'Zend/Gdata/Media/Extension/MediaKeywords.php';

/**
 * @see Zend_Gdata_Media_Extension_MediaPlayer
 */
#require_once 'Zend/Gdata/Media/Extension/MediaPlayer.php';

/**
 * @see Zend_Gdata_Media_Extension_MediaRating
 */
#require_once 'Zend/Gdata/Media/Extension/MediaRating.php';

/**
 * @see Zend_Gdata_Media_Extension_MediaRestriction
 */
#require_once 'Zend/Gdata/Media/Extension/MediaRestriction.php';

/**
 * @see Zend_Gdata_Media_Extension_MediaText
 */
#require_once 'Zend/Gdata/Media/Extension/MediaText.php';

/**
 * @see Zend_Gdata_Media_Extension_MediaThumbnail
 */
#require_once 'Zend/Gdata/Media/Extension/MediaThumbnail.php';

/**
 * @see Zend_Gdata_Media_Extension_MediaTitle
 */
#require_once 'Zend/Gdata/Media/Extension/MediaTitle.php';


/**
 * This class represents the media:group element of Media RSS.
 * It allows the grouping of media:content elements that are
 * different representations of the same content.  When it exists,
 * it is a child of an Entry (Atom) or Item (RSS).
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Media
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Media_Extension_MediaGroup extends Zend_Gdata_Extension
{

    protected $_rootElement = 'group';
    protected $_rootNamespace = 'media';

    /**
     * @var array
     */
    protected $_content = array();

    /**
     * @var array
     */
    protected $_category = array();

    /**
     * @var Zend_Gdata_Media_Extension_MediaCopyright
     */
    protected $_copyright = null;

    /**
     * @var array
     */
    protected $_credit = array();

    /**
     * @var Zend_Gdata_Media_Extension_MediaDescription
     */
    protected $_description = null;

    /**
     * @var array
     */
    protected $_hash = array();

    /**
     * @var Zend_Gdata_Media_Extension_MediaKeywords
     */
    protected $_keywords = null;

    /**
     * @var array
     */
    protected $_player = array();

    /**
     * @var array
     */
    protected $_rating = array();

    /**
     * @var array
     */
    protected $_restriction = array();

    /**
     * @var array
     */
    protected $_mediaText = array();

    /**
     * @var array
     */
    protected $_thumbnail = array();

    /**
     * @var string
     */
    protected $_title = null;

    /**
     * Creates an individual MediaGroup object.
     */
    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Media::$namespaces);
        parent::__construct($element);
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
        foreach ($this->_content as $content) {
            $element->appendChild($content->getDOM($element->ownerDocument));
        }
        foreach ($this->_category as $category) {
            $element->appendChild($category->getDOM($element->ownerDocument));
        }
        foreach ($this->_credit as $credit) {
            $element->appendChild($credit->getDOM($element->ownerDocument));
        }
        foreach ($this->_player as $player) {
            $element->appendChild($player->getDOM($element->ownerDocument));
        }
        foreach ($this->_rating as $rating) {
            $element->appendChild($rating->getDOM($element->ownerDocument));
        }
        foreach ($this->_restriction as $restriction) {
            $element->appendChild($restriction->getDOM($element->ownerDocument));
        }
        foreach ($this->_mediaText as $text) {
            $element->appendChild($text->getDOM($element->ownerDocument));
        }
        foreach ($this->_thumbnail as $thumbnail) {
            $element->appendChild($thumbnail->getDOM($element->ownerDocument));
        }
        if ($this->_copyright != null) {
            $element->appendChild(
                    $this->_copyright->getDOM($element->ownerDocument));
        }
        if ($this->_description != null) {
            $element->appendChild(
                    $this->_description->getDOM($element->ownerDocument));
        }
        foreach ($this->_hash as $hash) {
            $element->appendChild($hash->getDOM($element->ownerDocument));
        }
        if ($this->_keywords != null) {
            $element->appendChild(
                    $this->_keywords->getDOM($element->ownerDocument));
        }
        if ($this->_title != null) {
            $element->appendChild(
                    $this->_title->getDOM($element->ownerDocument));
        }
        return $element;
    }

    /**
     * Creates individual Entry objects of the appropriate type and
     * stores them in the $_entry array based upon DOM data.
     *
     * @param DOMNode $child The DOMNode to process
     */
    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;
        switch ($absoluteNodeName) {
            case $this->lookupNamespace('media') . ':' . 'content';
                $content = new Zend_Gdata_Media_Extension_MediaContent();
                $content->transferFromDOM($child);
                $this->_content[] = $content;
                break;
            case $this->lookupNamespace('media') . ':' . 'category';
                $category = new Zend_Gdata_Media_Extension_MediaCategory();
                $category->transferFromDOM($child);
                $this->_category[] = $category;
                break;
            case $this->lookupNamespace('media') . ':' . 'copyright';
                $copyright = new Zend_Gdata_Media_Extension_MediaCopyright();
                $copyright->transferFromDOM($child);
                $this->_copyright = $copyright;
                break;
            case $this->lookupNamespace('media') . ':' . 'credit';
                $credit = new Zend_Gdata_Media_Extension_MediaCredit();
                $credit->transferFromDOM($child);
                $this->_credit[] = $credit;
                break;
            case $this->lookupNamespace('media') . ':' . 'description';
                $description = new Zend_Gdata_Media_Extension_MediaDescription();
                $description->transferFromDOM($child);
                $this->_description = $description;
                break;
            case $this->lookupNamespace('media') . ':' . 'hash';
                $hash = new Zend_Gdata_Media_Extension_MediaHash();
                $hash->transferFromDOM($child);
                $this->_hash[] = $hash;
                break;
            case $this->lookupNamespace('media') . ':' . 'keywords';
                $keywords = new Zend_Gdata_Media_Extension_MediaKeywords();
                $keywords->transferFromDOM($child);
                $this->_keywords = $keywords;
                break;
            case $this->lookupNamespace('media') . ':' . 'player';
                $player = new Zend_Gdata_Media_Extension_MediaPlayer();
                $player->transferFromDOM($child);
                $this->_player[] = $player;
                break;
            case $this->lookupNamespace('media') . ':' . 'rating';
                $rating = new Zend_Gdata_Media_Extension_MediaRating();
                $rating->transferFromDOM($child);
                $this->_rating[] = $rating;
                break;
            case $this->lookupNamespace('media') . ':' . 'restriction';
                $restriction = new Zend_Gdata_Media_Extension_MediaRestriction();
                $restriction->transferFromDOM($child);
                $this->_restriction[] = $restriction;
                break;
            case $this->lookupNamespace('media') . ':' . 'text';
                $text = new Zend_Gdata_Media_Extension_MediaText();
                $text->transferFromDOM($child);
                $this->_mediaText[] = $text;
                break;
            case $this->lookupNamespace('media') . ':' . 'thumbnail';
                $thumbnail = new Zend_Gdata_Media_Extension_MediaThumbnail();
                $thumbnail->transferFromDOM($child);
                $this->_thumbnail[] = $thumbnail;
                break;
            case $this->lookupNamespace('media') . ':' . 'title';
                $title = new Zend_Gdata_Media_Extension_MediaTitle();
                $title->transferFromDOM($child);
                $this->_title = $title;
                break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    /**
     * @return array
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * @param array $value
     * @return Zend_Gdata_Media_MediaGroup Provides a fluent interface
     */
    public function setContent($value)
    {
        $this->_content = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getCategory()
    {
        return $this->_category;
    }

    /**
     * @param array $value
     * @return Zend_Gdata_Media_Extension_MediaGroup
     */
    public function setCategory($value)
    {
        $this->_category = $value;
        return $this;
    }

    /**
     * @return Zend_Gdata_Media_Extension_MediaCopyright
     */
    public function getCopyright()
    {
        return $this->_copyright;
    }

    /**
     * @param Zend_Gdata_Media_Extension_MediaCopyright $value
     * @return Zend_Gdata_Media_Extension_MediaGroup
     */
    public function setCopyright($value)
    {
        $this->_copyright = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getCredit()
    {
        return $this->_credit;
    }

    /**
     * @param array $value
     * @return Zend_Gdata_Media_Extension_MediaGroup
     */
    public function setCredit($value)
    {
        $this->_credit = $value;
        return $this;
    }

    /**
     * @return Zend_Gdata_Media_Extension_MediaTitle
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * @param Zend_Gdata_Media_Extension_MediaTitle $value
     * @return Zend_Gdata_Media_Extension_MediaGroup
     */
    public function setTitle($value)
    {
        $this->_title = $value;
        return $this;
    }

    /**
     * @return Zend_Gdata_Media_Extension_MediaDescription
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @param Zend_Gdata_Media_Extension_MediaDescription $value
     * @return Zend_Gdata_Media_Extension_MediaGroup
     */
    public function setDescription($value)
    {
        $this->_description = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getHash()
    {
        return $this->_hash;
    }

    /**
     * @param array $value
     * @return Zend_Gdata_Media_Extension_MediaGroup
     */
    public function setHash($value)
    {
        $this->_hash = $value;
        return $this;
    }

    /**
     * @return Zend_Gdata_Media_Extension_MediaKeywords
     */
    public function getKeywords()
    {
        return $this->_keywords;
    }

    /**
     * @param array $value
     * @return Zend_Gdata_Media_Extension_MediaGroup Provides a fluent interface
     */
    public function setKeywords($value)
    {
        $this->_keywords = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getPlayer()
    {
        return $this->_player;
    }

    /**
     * @param array
     * @return Zend_Gdata_Media_Extension_MediaGroup
     */
    public function setPlayer($value)
    {
        $this->_player = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getRating()
    {
        return $this->_rating;
    }

    /**
     * @param array
     * @return Zend_Gdata_Media_Extension_MediaGroup
     */
    public function setRating($value)
    {
        $this->_rating = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getRestriction()
    {
        return $this->_restriction;
    }

    /**
     * @param array
     * @return Zend_Gdata_Media_Extension_MediaGroup
     */
    public function setRestriction($value)
    {
        $this->_restriction = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getThumbnail()
    {
        return $this->_thumbnail;
    }

    /**
     * @param array
     * @return Zend_Gdata_Media_Extension_MediaGroup
     */
    public function setThumbnail($value)
    {
        $this->_thumbnail = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getMediaText()
    {
        return $this->_mediaText;
    }

    /**
     * @param array
     * @return Zend_Gdata_Media_Extension_MediaGroup
     */
    public function setMediaText($value)
    {
        $this->_mediaText = $value;
        return $this;
    }

}
