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
 * @see Zend_Gdata_Media_Extension_MediaGroup
 */
#require_once 'Zend/Gdata/Media/Extension/MediaGroup.php';

/**
 * @see Zend_Gdata_YouTube_Extension_MediaContent
 */
#require_once 'Zend/Gdata/YouTube/Extension/MediaContent.php';

/**
 * @see Zend_Gdata_YouTube_Extension_Duration
 */
#require_once 'Zend/Gdata/YouTube/Extension/Duration.php';

/**
 * @see Zend_Gdata_YouTube_Extension_MediaRating
 */
#require_once 'Zend/Gdata/YouTube/Extension/MediaRating.php';

/**
 * @see Zend_Gdata_YouTube_Extension_MediaCredit
 */
#require_once 'Zend/Gdata/YouTube/Extension/MediaCredit.php';

/**
 * @see Zend_Gdata_YouTube_Extension_Private
 */
#require_once 'Zend/Gdata/YouTube/Extension/Private.php';

/**
 * @see Zend_Gdata_YouTube_Extension_VideoId
 */
#require_once 'Zend/Gdata/YouTube/Extension/VideoId.php';

/**
 * @see Zend_Gdata_YouTube_Extension_Uploaded
 */
#require_once 'Zend/Gdata/YouTube/Extension/Uploaded.php';

/**
 * This class represents the media:group element of Media RSS.
 * It allows the grouping of media:content elements that are
 * different representations of the same content.  When it exists,
 * it is a child of an Entry (Atom) or Item (RSS).
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_YouTube_Extension_MediaGroup extends Zend_Gdata_Media_Extension_MediaGroup
{

    protected $_rootElement = 'group';
    protected $_rootNamespace = 'media';

    /**
     * @var Zend_Gdata_YouTube_Extension_Duration
     */
    protected $_duration = null;

    /**
     * @var Zend_Gdata_YouTube_Extension_Private
     */
    protected $_private = null;

    /**
     * @var Zend_Gdata_YouTube_Extension_VideoId
     */
    protected $_videoid = null;

    /**
     * @var Zend_Gdata_YouTube_Extension_MediaRating
     */
    protected $_mediarating = null;

    /**
     * @var Zend_Gdata_YouTube_Extension_MediaCredit
     */
    protected $_mediacredit = null;

    /**
     * @var Zend_Gdata_YouTube_Extension_Uploaded
     */
    protected $_uploaded = null;

    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
        parent::__construct($element);
    }

    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_duration !== null) {
            $element->appendChild(
                $this->_duration->getDOM($element->ownerDocument));
        }
        if ($this->_private !== null) {
            $element->appendChild(
                $this->_private->getDOM($element->ownerDocument));
        }
        if ($this->_videoid != null) {
            $element->appendChild(
                $this->_videoid->getDOM($element->ownerDocument));
        }
        if ($this->_uploaded != null) {
            $element->appendChild(
                $this->_uploaded->getDOM($element->ownerDocument));
        }
        if ($this->_mediacredit != null) {
            $element->appendChild(
                $this->_mediacredit->getDOM($element->ownerDocument));
        }
        if ($this->_mediarating != null) {
            $element->appendChild(
                $this->_mediarating->getDOM($element->ownerDocument));
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
            case $this->lookupNamespace('media') . ':' . 'content':
                $content = new Zend_Gdata_YouTube_Extension_MediaContent();
                $content->transferFromDOM($child);
                $this->_content[] = $content;
                break;
            case $this->lookupNamespace('media') . ':' . 'rating':
                $mediarating = new Zend_Gdata_YouTube_Extension_MediaRating();
                $mediarating->transferFromDOM($child);
                $this->_mediarating = $mediarating;
                break;
            case $this->lookupNamespace('media') . ':' . 'credit':
                $mediacredit = new Zend_Gdata_YouTube_Extension_MediaCredit();
                $mediacredit->transferFromDOM($child);
                $this->_mediacredit = $mediacredit;
                break;
            case $this->lookupNamespace('yt') . ':' . 'duration':
                $duration = new Zend_Gdata_YouTube_Extension_Duration();
                $duration->transferFromDOM($child);
                $this->_duration = $duration;
                break;
            case $this->lookupNamespace('yt') . ':' . 'private':
                $private = new Zend_Gdata_YouTube_Extension_Private();
                $private->transferFromDOM($child);
                $this->_private = $private;
                break;
            case $this->lookupNamespace('yt') . ':' . 'videoid':
                $videoid = new Zend_Gdata_YouTube_Extension_VideoId();
                $videoid ->transferFromDOM($child);
                $this->_videoid = $videoid;
                break;
            case $this->lookupNamespace('yt') . ':' . 'uploaded':
                $uploaded = new Zend_Gdata_YouTube_Extension_Uploaded();
                $uploaded ->transferFromDOM($child);
                $this->_uploaded = $uploaded;
                break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    /**
     * Returns the duration value of this element
     *
     * @return Zend_Gdata_YouTube_Extension_Duration
     */
    public function getDuration()
    {
        return $this->_duration;
    }

    /**
     * Sets the duration value of this element
     *
     * @param Zend_Gdata_YouTube_Extension_Duration $value The duration value
     * @return Zend_Gdata_YouTube_Extension_MediaGroup Provides a fluent
     *         interface
     */
    public function setDuration($value)
    {
        $this->_duration = $value;
        return $this;
    }

    /**
     * Returns the videoid value of this element
     *
     * @return Zend_Gdata_YouTube_Extension_VideoId
     */
    public function getVideoId()
    {
        return $this->_videoid;
    }

    /**
     * Sets the videoid value of this element
     *
     * @param Zend_Gdata_YouTube_Extension_VideoId $value The video id value
     * @return Zend_Gdata_YouTube_Extension_MediaGroup Provides a fluent
     *         interface
     */
    public function setVideoId($value)
    {
        $this->_videoid = $value;
        return $this;
    }

    /**
     * Returns the yt:uploaded element
     *
     * @return Zend_Gdata_YouTube_Extension_Uploaded
     */
    public function getUploaded()
    {
        return $this->_uploaded;
    }

    /**
     * Sets the yt:uploaded element
     *
     * @param Zend_Gdata_YouTube_Extension_Uploaded $value The uploaded value
     * @return Zend_Gdata_YouTube_Extension_MediaGroup Provides a fluent
     *         interface
     */
    public function setUploaded($value)
    {
        $this->_uploaded = $value;
        return $this;
    }

    /**
     * Returns the private value of this element
     *
     * @return Zend_Gdata_YouTube_Extension_Private
     */
    public function getPrivate()
    {
        return $this->_private;
    }

    /**
     * Sets the private value of this element
     *
     * @param Zend_Gdata_YouTube_Extension_Private $value The private value
     * @return Zend_Gdata_YouTube_Extension_MediaGroup Provides a fluent
     *         interface
     */
    public function setPrivate($value)
    {
        $this->_private = $value;
        return $this;
    }

    /**
     * Returns the rating value of this element
     *
     * @return Zend_Gdata_YouTube_Extension_MediaRating
     */
    public function getMediaRating()
    {
        return $this->_mediarating;
    }

    /**
     * Sets the media:rating value of this element
     *
     * @param Zend_Gdata_YouTube_Extension_MediaRating $value The rating element
     * @return Zend_Gdata_YouTube_Extension_MediaGroup Provides a fluent
     *         interface
     */
    public function setMediaRating($value)
    {
        $this->_mediarating = $value;
        return $this;
    }

    /**
     * Returns the media:credit value of this element
     *
     * @return Zend_Gdata_YouTube_Extension_MediaCredit
     */
    public function getMediaCredit()
    {
        return $this->_mediacredit;
    }

    /**
     * Sets the media:credit value of this element
     *
     * @param Zend_Gdata_YouTube_Extension_MediaCredit $value The credit element
     * @return Zend_Gdata_YouTube_Extension_MediaGroup Provides a fluent
     *         interface
     */
    public function setMediaCredit($value)
    {
        $this->_mediacredit = $value;
        return $this;
    }
}
