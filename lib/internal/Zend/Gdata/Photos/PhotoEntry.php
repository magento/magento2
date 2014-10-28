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
 * @subpackage Photos
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: PhotoEntry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_MediaEntry
 */
#require_once 'Zend/Gdata/Media/Entry.php';

/**
 * @see Zend_Gdata_Photos_Extension_PhotoId
 */
#require_once 'Zend/Gdata/Photos/Extension/PhotoId.php';

/**
 * @see Zend_Gdata_Photos_Extension_Version
 */
#require_once 'Zend/Gdata/Photos/Extension/Version.php';

/**
 * @see Zend_Gdata_Photos_Extension_AlbumId
 */
#require_once 'Zend/Gdata/Photos/Extension/AlbumId.php';

/**
 * @see Zend_Gdata_Photos_Extension_Id
 */
#require_once 'Zend/Gdata/Photos/Extension/Id.php';

/**
 * @see Zend_Gdata_Photos_Extension_Width
 */
#require_once 'Zend/Gdata/Photos/Extension/Width.php';

/**
 * @see Zend_Gdata_Photos_Extension_Height
 */
#require_once 'Zend/Gdata/Photos/Extension/Height.php';

/**
 * @see Zend_Gdata_Photos_Extension_Size
 */
#require_once 'Zend/Gdata/Photos/Extension/Size.php';

/**
 * @see Zend_Gdata_Photos_Extension_Client
 */
#require_once 'Zend/Gdata/Photos/Extension/Client.php';

/**
 * @see Zend_Gdata_Photos_Extension_Checksum
 */
#require_once 'Zend/Gdata/Photos/Extension/Checksum.php';

/**
 * @see Zend_Gdata_Photos_Extension_Timestamp
 */
#require_once 'Zend/Gdata/Photos/Extension/Timestamp.php';

/**
 * @see Zend_Gdata_Photos_Extension_CommentingEnabled
 */
#require_once 'Zend/Gdata/Photos/Extension/CommentingEnabled.php';

/**
 * @see Zend_Gdata_Photos_Extension_CommentCount
 */
#require_once 'Zend/Gdata/Photos/Extension/CommentCount.php';

/**
 * @see Zend_Gdata_Exif_Extension_Tags
 */
#require_once 'Zend/Gdata/Exif/Extension/Tags.php';

/**
 * @see Zend_Gdata_Geo_Extension_GeoRssWhere
 */
#require_once 'Zend/Gdata/Geo/Extension/GeoRssWhere.php';

/**
 * @see Zend_Gdata_App_Extension_Category
 */
#require_once 'Zend/Gdata/App/Extension/Category.php';

/**
 * Data model class for a Comment Entry.
 *
 * To transfer user entries to and from the servers, including
 * creating new entries, refer to the service class,
 * Zend_Gdata_Photos.
 *
 * This class represents <atom:entry> in the Google Data protocol.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Photos
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Photos_PhotoEntry extends Zend_Gdata_Media_Entry
{

    protected $_entryClassName = 'Zend_Gdata_Photos_PhotoEntry';

    /**
     * gphoto:id element
     *
     * @var Zend_Gdata_Photos_Extension_Id
     */
    protected $_gphotoId = null;

    /**
     * gphoto:albumid element
     *
     * @var Zend_Gdata_Photos_Extension_AlbumId
     */
    protected $_gphotoAlbumId = null;

    /**
     * gphoto:version element
     *
     * @var Zend_Gdata_Photos_Extension_Version
     */
    protected $_gphotoVersion = null;

    /**
     * gphoto:width element
     *
     * @var Zend_Gdata_Photos_Extension_Width
     */
    protected $_gphotoWidth = null;

    /**
     * gphoto:height element
     *
     * @var Zend_Gdata_Photos_Extension_Height
     */
    protected $_gphotoHeight = null;

    /**
     * gphoto:size element
     *
     * @var Zend_Gdata_Photos_Extension_Size
     */
    protected $_gphotoSize = null;

    /**
     * gphoto:client element
     *
     * @var Zend_Gdata_Photos_Extension_Client
     */
    protected $_gphotoClient = null;

    /**
     * gphoto:checksum element
     *
     * @var Zend_Gdata_Photos_Extension_Checksum
     */
    protected $_gphotoChecksum = null;

    /**
     * gphoto:timestamp element
     *
     * @var Zend_Gdata_Photos_Extension_Timestamp
     */
    protected $_gphotoTimestamp = null;

    /**
     * gphoto:commentCount element
     *
     * @var Zend_Gdata_Photos_Extension_CommentCount
     */
    protected $_gphotoCommentCount = null;

    /**
     * gphoto:commentingEnabled element
     *
     * @var Zend_Gdata_Photos_Extension_CommentingEnabled
     */
    protected $_gphotoCommentingEnabled = null;

    /**
     * exif:tags element
     *
     * @var Zend_Gdata_Exif_Extension_Tags
     */
    protected $_exifTags = null;

    /**
     * georss:where element
     *
     * @var Zend_Gdata_Geo_Extension_GeoRssWhere
     */
    protected $_geoRssWhere = null;

    /**
     * Create a new instance.
     *
     * @param DOMElement $element (optional) DOMElement from which this
     *          object should be constructed.
     */
    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Photos::$namespaces);
        parent::__construct($element);

        $category = new Zend_Gdata_App_Extension_Category(
            'http://schemas.google.com/photos/2007#photo',
            'http://schemas.google.com/g/2005#kind');
        $this->setCategory(array($category));
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     *          child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_gphotoAlbumId !== null) {
            $element->appendChild($this->_gphotoAlbumId->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoId !== null) {
            $element->appendChild($this->_gphotoId->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoVersion !== null) {
            $element->appendChild($this->_gphotoVersion->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoWidth !== null) {
            $element->appendChild($this->_gphotoWidth->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoHeight !== null) {
            $element->appendChild($this->_gphotoHeight->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoSize !== null) {
            $element->appendChild($this->_gphotoSize->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoClient !== null) {
            $element->appendChild($this->_gphotoClient->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoChecksum !== null) {
            $element->appendChild($this->_gphotoChecksum->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoTimestamp !== null) {
            $element->appendChild($this->_gphotoTimestamp->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoCommentingEnabled !== null) {
            $element->appendChild($this->_gphotoCommentingEnabled->getDOM($element->ownerDocument));
        }
        if ($this->_gphotoCommentCount !== null) {
            $element->appendChild($this->_gphotoCommentCount->getDOM($element->ownerDocument));
        }
        if ($this->_exifTags !== null) {
            $element->appendChild($this->_exifTags->getDOM($element->ownerDocument));
        }
        if ($this->_geoRssWhere !== null) {
            $element->appendChild($this->_geoRssWhere->getDOM($element->ownerDocument));
        }
        return $element;
    }

    /**
     * Creates individual Entry objects of the appropriate type and
     * stores them as members of this entry based upon DOM data.
     *
     * @param DOMNode $child The DOMNode to process
     */
    protected function takeChildFromDOM($child)
    {
        $absoluteNodeName = $child->namespaceURI . ':' . $child->localName;

        switch ($absoluteNodeName) {
            case $this->lookupNamespace('gphoto') . ':' . 'albumid';
                $albumId = new Zend_Gdata_Photos_Extension_AlbumId();
                $albumId->transferFromDOM($child);
                $this->_gphotoAlbumId = $albumId;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'id';
                $id = new Zend_Gdata_Photos_Extension_Id();
                $id->transferFromDOM($child);
                $this->_gphotoId = $id;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'version';
                $version = new Zend_Gdata_Photos_Extension_Version();
                $version->transferFromDOM($child);
                $this->_gphotoVersion = $version;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'width';
                $width = new Zend_Gdata_Photos_Extension_Width();
                $width->transferFromDOM($child);
                $this->_gphotoWidth = $width;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'height';
                $height = new Zend_Gdata_Photos_Extension_Height();
                $height->transferFromDOM($child);
                $this->_gphotoHeight = $height;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'size';
                $size = new Zend_Gdata_Photos_Extension_Size();
                $size->transferFromDOM($child);
                $this->_gphotoSize = $size;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'client';
                $client = new Zend_Gdata_Photos_Extension_Client();
                $client->transferFromDOM($child);
                $this->_gphotoClient = $client;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'checksum';
                $checksum = new Zend_Gdata_Photos_Extension_Checksum();
                $checksum->transferFromDOM($child);
                $this->_gphotoChecksum = $checksum;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'timestamp';
                $timestamp = new Zend_Gdata_Photos_Extension_Timestamp();
                $timestamp->transferFromDOM($child);
                $this->_gphotoTimestamp = $timestamp;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'commentingEnabled';
                $commentingEnabled = new Zend_Gdata_Photos_Extension_CommentingEnabled();
                $commentingEnabled->transferFromDOM($child);
                $this->_gphotoCommentingEnabled = $commentingEnabled;
                break;
            case $this->lookupNamespace('gphoto') . ':' . 'commentCount';
                $commentCount = new Zend_Gdata_Photos_Extension_CommentCount();
                $commentCount->transferFromDOM($child);
                $this->_gphotoCommentCount = $commentCount;
                break;
            case $this->lookupNamespace('exif') . ':' . 'tags';
                $exifTags = new Zend_Gdata_Exif_Extension_Tags();
                $exifTags->transferFromDOM($child);
                $this->_exifTags = $exifTags;
                break;
            case $this->lookupNamespace('georss') . ':' . 'where';
                $geoRssWhere = new Zend_Gdata_Geo_Extension_GeoRssWhere();
                $geoRssWhere->transferFromDOM($child);
                $this->_geoRssWhere = $geoRssWhere;
                break;
            default:
                parent::takeChildFromDOM($child);
                break;

        }
    }

    /**
     * Get the value for this element's gphoto:albumid attribute.
     *
     * @see setGphotoAlbumId
     * @return string The requested attribute.
     */
    public function getGphotoAlbumId()
    {
        return $this->_gphotoAlbumId;
    }

    /**
     * Set the value for this element's gphoto:albumid attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_AlbumId The element being modified.
     */
    public function setGphotoAlbumId($value)
    {
        $this->_gphotoAlbumId = $value;
        return $this;
    }

    /**
     * Get the value for this element's gphoto:id attribute.
     *
     * @see setGphotoId
     * @return string The requested attribute.
     */
    public function getGphotoId()
    {
        return $this->_gphotoId;
    }

    /**
     * Set the value for this element's gphoto:id attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_Id The element being modified.
     */
    public function setGphotoId($value)
    {
        $this->_gphotoId = $value;
        return $this;
    }

    /**
     * Get the value for this element's gphoto:version attribute.
     *
     * @see setGphotoVersion
     * @return string The requested attribute.
     */
    public function getGphotoVersion()
    {
        return $this->_gphotoVersion;
    }

    /**
     * Set the value for this element's gphoto:version attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_Version The element being modified.
     */
    public function setGphotoVersion($value)
    {
        $this->_gphotoVersion = $value;
        return $this;
    }

    /**
     * Get the value for this element's gphoto:width attribute.
     *
     * @see setGphotoWidth
     * @return string The requested attribute.
     */
    public function getGphotoWidth()
    {
        return $this->_gphotoWidth;
    }

    /**
     * Set the value for this element's gphoto:width attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_Width The element being modified.
     */
    public function setGphotoWidth($value)
    {
        $this->_gphotoWidth = $value;
        return $this;
    }

    /**
     * Get the value for this element's gphoto:height attribute.
     *
     * @see setGphotoHeight
     * @return string The requested attribute.
     */
    public function getGphotoHeight()
    {
        return $this->_gphotoHeight;
    }

    /**
     * Set the value for this element's gphoto:height attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_Height The element being modified.
     */
    public function setGphotoHeight($value)
    {
        $this->_gphotoHeight = $value;
        return $this;
    }

    /**
     * Get the value for this element's gphoto:size attribute.
     *
     * @see setGphotoSize
     * @return string The requested attribute.
     */
    public function getGphotoSize()
    {
        return $this->_gphotoSize;
    }

    /**
     * Set the value for this element's gphoto:size attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_Size The element being modified.
     */
    public function setGphotoSize($value)
    {
        $this->_gphotoSize = $value;
        return $this;
    }

    /**
     * Get the value for this element's gphoto:client attribute.
     *
     * @see setGphotoClient
     * @return string The requested attribute.
     */
    public function getGphotoClient()
    {
        return $this->_gphotoClient;
    }

    /**
     * Set the value for this element's gphoto:client attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_Client The element being modified.
     */
    public function setGphotoClient($value)
    {
        $this->_gphotoClient = $value;
        return $this;
    }

    /**
     * Get the value for this element's gphoto:checksum attribute.
     *
     * @see setGphotoChecksum
     * @return string The requested attribute.
     */
    public function getGphotoChecksum()
    {
        return $this->_gphotoChecksum;
    }

    /**
     * Set the value for this element's gphoto:checksum attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_Checksum The element being modified.
     */
    public function setGphotoChecksum($value)
    {
        $this->_gphotoChecksum = $value;
        return $this;
    }

    /**
     * Get the value for this element's gphoto:timestamp attribute.
     *
     * @see setGphotoTimestamp
     * @return string The requested attribute.
     */
    public function getGphotoTimestamp()
    {
        return $this->_gphotoTimestamp;
    }

    /**
     * Set the value for this element's gphoto:timestamp attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_Timestamp The element being modified.
     */
    public function setGphotoTimestamp($value)
    {
        $this->_gphotoTimestamp = $value;
        return $this;
    }

    /**
     * Get the value for this element's gphoto:commentCount attribute.
     *
     * @see setGphotoCommentCount
     * @return string The requested attribute.
     */
    public function getGphotoCommentCount()
    {
        return $this->_gphotoCommentCount;
    }

    /**
     * Set the value for this element's gphoto:commentCount attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_CommentCount The element being modified.
     */
    public function setGphotoCommentCount($value)
    {
        $this->_gphotoCommentCount = $value;
        return $this;
    }

    /**
     * Get the value for this element's gphoto:commentingEnabled attribute.
     *
     * @see setGphotoCommentingEnabled
     * @return string The requested attribute.
     */
    public function getGphotoCommentingEnabled()
    {
        return $this->_gphotoCommentingEnabled;
    }

    /**
     * Set the value for this element's gphoto:commentingEnabled attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Photos_Extension_CommentingEnabled The element being modified.
     */
    public function setGphotoCommentingEnabled($value)
    {
        $this->_gphotoCommentingEnabled = $value;
        return $this;
    }

    /**
     * Get the value for this element's exif:tags attribute.
     *
     * @see setExifTags
     * @return string The requested attribute.
     */
    public function getExifTags()
    {
        return $this->_exifTags;
    }

    /**
     * Set the value for this element's exif:tags attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Exif_Extension_Tags The element being modified.
     */
    public function setExifTags($value)
    {
        $this->_exifTags = $value;
        return $this;
    }

    /**
     * Get the value for this element's georss:where attribute.
     *
     * @see setGeoRssWhere
     * @return string The requested attribute.
     */
    public function getGeoRssWhere()
    {
        return $this->_geoRssWhere;
    }

    /**
     * Set the value for this element's georss:where attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Geo_Extension_GeoRssWhere The element being modified.
     */
    public function setGeoRssWhere($value)
    {
        $this->_geoRssWhere = $value;
        return $this;
    }

    /**
     * Get the value for this element's media:group attribute.
     *
     * @see setMediaGroup
     * @return string The requested attribute.
     */
    public function getMediaGroup()
    {
        return $this->_mediaGroup;
    }

    /**
     * Set the value for this element's media:group attribute.
     *
     * @param string $value The desired value for this attribute.
     * @return Zend_Gdata_Media_Extension_MediaGroup The element being modified.
     */
    public function setMediaGroup($value)
    {
        $this->_mediaGroup = $value;
        return $this;
    }

}
