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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: VideoEntry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Extension_Comments
 */
#require_once 'Zend/Gdata/Extension/Comments.php';

/**
 * @see Zend_Gdata_Extension_FeedLink
 */
#require_once 'Zend/Gdata/Extension/FeedLink.php';

/**
 * @see Zend_Gdata_YouTube_MediaEntry
 */
#require_once 'Zend/Gdata/YouTube/MediaEntry.php';

/**
 * @see Zend_Gdata_YouTube_Extension_MediaGroup
 */
#require_once 'Zend/Gdata/YouTube/Extension/MediaGroup.php';

/**
 * @see Zend_Gdata_YouTube_Extension_NoEmbed
 */
#require_once 'Zend/Gdata/YouTube/Extension/NoEmbed.php';

/**
 * @see Zend_Gdata_YouTube_Extension_Statistics
 */
#require_once 'Zend/Gdata/YouTube/Extension/Statistics.php';

/**
 * @see Zend_Gdata_YouTube_Extension_Link
 */
#require_once 'Zend/Gdata/YouTube/Extension/Link.php';

/**
 * @see Zend_Gdata_YouTube_Extension_Racy
 */
#require_once 'Zend/Gdata/YouTube/Extension/Racy.php';

/**
 * @see Zend_Gdata_Extension_Rating
 */
#require_once 'Zend/Gdata/Extension/Rating.php';

/**
 * @see Zend_Gdata_Geo_Extension_GeoRssWhere
 */
#require_once 'Zend/Gdata/Geo/Extension/GeoRssWhere.php';

/**
 * @see Zend_Gdata_YouTube_Extension_Control
 */
#require_once 'Zend/Gdata/YouTube/Extension/Control.php';

/**
 * @see Zend_Gdata_YouTube_Extension_Recorded
 */
#require_once 'Zend/Gdata/YouTube/Extension/Recorded.php';

/**
 * @see Zend_Gdata_YouTube_Extension_Location
 */
#require_once 'Zend/Gdata/YouTube/Extension/Location.php';

/**
 * Represents the YouTube video flavor of an Atom entry
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_YouTube_VideoEntry extends Zend_Gdata_YouTube_MediaEntry
{

    const YOUTUBE_DEVELOPER_TAGS_SCHEMA = 'http://gdata.youtube.com/schemas/2007/developertags.cat';
    const YOUTUBE_CATEGORY_SCHEMA = 'http://gdata.youtube.com/schemas/2007/categories.cat';
    protected $_entryClassName = 'Zend_Gdata_YouTube_VideoEntry';

    /**
     * If null, the video can be embedded
     *
     * @var Zend_Gdata_YouTube_Extension_NoEmbed|null
     */
    protected $_noEmbed = null;

    /**
     * Specifies the statistics relating to the video.
     *
     * @var Zend_Gdata_YouTube_Extension_Statistics
     */
    protected $_statistics = null;

    /**
     * If not null, specifies that the video has racy content.
     *
     * @var Zend_Gdata_YouTube_Extension_Racy|null
     */
    protected $_racy = null;

    /**
     * If not null, specifies that the video is private.
     *
     * @var Zend_Gdata_YouTube_Extension_Private|null
     */
    protected $_private = null;

    /**
     * Specifies the video's rating.
     *
     * @var Zend_Gdata_Extension_Rating
     */
    protected $_rating = null;

    /**
     * Specifies the comments associated with a video.
     *
     * @var Zend_Gdata_Extensions_Comments
     */
    protected $_comments = null;

    /**
     * Nested feed links
     *
     * @var array
     */
    protected $_feedLink = array();

    /**
     * Geo location for the video
     *
     * @var Zend_Gdata_Geo_Extension_GeoRssWhere
     */
    protected $_where = null;

    /**
     * Recording date for the video
     *
     * @var Zend_Gdata_YouTube_Extension_Recorded|null
     */
    protected $_recorded = null;

    /**
     * Location informtion for the video
     *
     * @var Zend_Gdata_YouTube_Extension_Location|null
     */
    protected $_location = null;

    /**
     * Creates a Video entry, representing an individual video
     *
     * @param DOMElement $element (optional) DOMElement from which this
     *          object should be constructed.
     */
    public function __construct($element = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_YouTube::$namespaces);
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
        if ($this->_noEmbed != null) {
            $element->appendChild($this->_noEmbed->getDOM(
                $element->ownerDocument));
        }
        if ($this->_statistics != null) {
            $element->appendChild($this->_statistics->getDOM(
                $element->ownerDocument));
        }
        if ($this->_racy != null) {
            $element->appendChild($this->_racy->getDOM(
                $element->ownerDocument));
        }
        if ($this->_recorded != null) {
            $element->appendChild($this->_recorded->getDOM(
                $element->ownerDocument));
        }
        if ($this->_location != null) {
            $element->appendChild($this->_location->getDOM(
                $element->ownerDocument));
        }
        if ($this->_rating != null) {
            $element->appendChild($this->_rating->getDOM(
                $element->ownerDocument));
        }
        if ($this->_comments != null) {
            $element->appendChild($this->_comments->getDOM(
                $element->ownerDocument));
        }
        if ($this->_feedLink != null) {
            foreach ($this->_feedLink as $feedLink) {
                $element->appendChild($feedLink->getDOM(
                    $element->ownerDocument));
            }
        }
        if ($this->_where != null) {
           $element->appendChild($this->_where->getDOM(
                $element->ownerDocument));
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
        case $this->lookupNamespace('yt') . ':' . 'statistics':
            $statistics = new Zend_Gdata_YouTube_Extension_Statistics();
            $statistics->transferFromDOM($child);
            $this->_statistics = $statistics;
            break;
        case $this->lookupNamespace('yt') . ':' . 'racy':
            $racy = new Zend_Gdata_YouTube_Extension_Racy();
            $racy->transferFromDOM($child);
            $this->_racy = $racy;
            break;
        case $this->lookupNamespace('yt') . ':' . 'recorded':
            $recorded = new Zend_Gdata_YouTube_Extension_Recorded();
            $recorded->transferFromDOM($child);
            $this->_recorded = $recorded;
            break;
        case $this->lookupNamespace('yt') . ':' . 'location':
            $location = new Zend_Gdata_YouTube_Extension_Location();
            $location->transferFromDOM($child);
            $this->_location = $location;
            break;
        case $this->lookupNamespace('gd') . ':' . 'rating':
            $rating = new Zend_Gdata_Extension_Rating();
            $rating->transferFromDOM($child);
            $this->_rating = $rating;
            break;
        case $this->lookupNamespace('gd') . ':' . 'comments':
            $comments = new Zend_Gdata_Extension_Comments();
            $comments->transferFromDOM($child);
            $this->_comments = $comments;
            break;
        case $this->lookupNamespace('yt') . ':' . 'noembed':
            $noEmbed = new Zend_Gdata_YouTube_Extension_NoEmbed();
            $noEmbed->transferFromDOM($child);
            $this->_noEmbed = $noEmbed;
            break;
        case $this->lookupNamespace('gd') . ':' . 'feedLink':
            $feedLink = new Zend_Gdata_Extension_FeedLink();
            $feedLink->transferFromDOM($child);
            $this->_feedLink[] = $feedLink;
            break;
        case $this->lookupNamespace('georss') . ':' . 'where':
            $where = new Zend_Gdata_Geo_Extension_GeoRssWhere();
            $where->transferFromDOM($child);
            $this->_where = $where;
            break;
        case $this->lookupNamespace('atom') . ':' . 'link';
            $link = new Zend_Gdata_YouTube_Extension_Link();
            $link->transferFromDOM($child);
            $this->_link[] = $link;
            break;
        case $this->lookupNamespace('app') . ':' . 'control':
            $control = new Zend_Gdata_YouTube_Extension_Control();
            $control->transferFromDOM($child);
            $this->_control = $control;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    /**
     * Sets when the video was recorded.
     *
     * @param Zend_Gdata_YouTube_Extension_Recorded $recorded When the video was recorded
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setRecorded($recorded = null)
    {
        $this->_recorded = $recorded;
        return $this;
    }

    /**
     * Gets the date that the video was recorded.
     *
     * @return Zend_Gdata_YouTube_Extension_Recorded|null
     */
    public function getRecorded()
    {
        return $this->_recorded;
    }

    /**
     * Sets the location information.
     *
     * @param Zend_Gdata_YouTube_Extension_Location $location Where the video
     *        was recorded
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setLocation($location = null)
    {
        $this->_location = $location;
        return $this;
    }

    /**
     * Gets the location where the video was recorded.
     *
     * @return Zend_Gdata_YouTube_Extension_Location|null
     */
    public function getLocation()
    {
        return $this->_location;
    }

    /**
     * If an instance of Zend_Gdata_YouTube_Extension_NoEmbed is passed in,
     * the video cannot be embedded.  Otherwise, if null is passsed in, the
     * video is able to be embedded.
     *
     * @param Zend_Gdata_YouTube_Extension_NoEmbed $noEmbed Whether or not the
     *          video can be embedded.
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setNoEmbed($noEmbed = null)
    {
        $this->_noEmbed = $noEmbed;
        return $this;
    }

    /**
     * If the return value is an instance of
     * Zend_Gdata_YouTube_Extension_NoEmbed, this video cannot be embedded.
     *
     * @return Zend_Gdata_YouTube_Extension_NoEmbed|null Whether or not the video can be embedded
     */
    public function getNoEmbed()
    {
        return $this->_noEmbed;
    }

    /**
     * Checks whether the video is embeddable.
     *
     * @return bool Returns true if the video is embeddable.
     */
    public function isVideoEmbeddable()
    {
        if ($this->getNoEmbed() == null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sets the statistics relating to the video.
     *
     * @param Zend_Gdata_YouTube_Extension_Statistics $statistics The statistics relating to the video
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setStatistics($statistics = null)
    {
        $this->_statistics = $statistics;
        return $this;
    }

    /**
     * Returns the statistics relating to the video.
     *
     * @return Zend_Gdata_YouTube_Extension_Statistics  The statistics relating to the video
     */
    public function getStatistics()
    {
        return $this->_statistics;
    }

    /**
     * Specifies that the video has racy content.
     *
     * @param Zend_Gdata_YouTube_Extension_Racy $racy The racy flag object
     * @throws Zend_Gdata_App_VersionException
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setRacy($racy = null)
    {
        if ($this->getMajorProtocolVersion() == 2) {
            #require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException(
                'Calling getRacy() on a YouTube VideoEntry is deprecated ' .
                'as of version 2 of the API.');
        }

        $this->_racy = $racy;
        return $this;
    }

    /**
     * Returns the racy flag object.
     *
     * @throws Zend_Gdata_App_VersionException
     * @return Zend_Gdata_YouTube_Extension_Racy|null  The racy flag object
     */
    public function getRacy()
    {
        if ($this->getMajorProtocolVersion() == 2) {
            #require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException(
                'Calling getRacy() on a YouTube VideoEntry is deprecated ' .
                'as of version 2 of the API.');
        }
        return $this->_racy;
    }

    /**
     * Sets the rating relating to the video.
     *
     * @param Zend_Gdata_Extension_Rating $rating The rating relating to the video
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setRating($rating = null)
    {
        $this->_rating = $rating;
        return $this;
    }

    /**
     * Returns the rating relating to the video.
     *
     * @return Zend_Gdata_Extension_Rating  The rating relating to the video
     */
    public function getRating()
    {
        return $this->_rating;
    }

    /**
     * Sets the comments relating to the video.
     *
     * @param Zend_Gdata_Extension_Comments $comments The comments relating to the video
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setComments($comments = null)
    {
        $this->_comments = $comments;
        return $this;
    }

    /**
     * Returns the comments relating to the video.
     *
     * @return Zend_Gdata_Extension_Comments  The comments relating to the video
     */
    public function getComments()
    {
        return $this->_comments;
    }

    /**
     * Sets the array of embedded feeds related to the video
     *
     * @param array $feedLink The array of embedded feeds relating to the video
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setFeedLink($feedLink = null)
    {
        $this->_feedLink = $feedLink;
        return $this;
    }

    /**
     * Get the feed link property for this entry.
     *
     * @see setFeedLink
     * @param string $rel (optional) The rel value of the link to be found.
     *          If null, the array of links is returned.
     * @return mixed If $rel is specified, a Zend_Gdata_Extension_FeedLink
     *          object corresponding to the requested rel value is returned
     *          if found, or null if the requested value is not found. If
     *          $rel is null or not specified, an array of all available
     *          feed links for this entry is returned, or null if no feed
     *          links are set.
     */
    public function getFeedLink($rel = null)
    {
        if ($rel == null) {
            return $this->_feedLink;
        } else {
            foreach ($this->_feedLink as $feedLink) {
                if ($feedLink->rel == $rel) {
                    return $feedLink;
                }
            }
            return null;
        }
    }

    /**
     * Returns the link element relating to video responses.
     *
     * @return Zend_Gdata_App_Extension_Link
     */
    public function getVideoResponsesLink()
    {
        return $this->getLink(Zend_Gdata_YouTube::VIDEO_RESPONSES_REL);
    }

    /**
     * Returns the link element relating to video ratings.
     *
     * @return Zend_Gdata_App_Extension_Link
     */
    public function getVideoRatingsLink()
    {
        return $this->getLink(Zend_Gdata_YouTube::VIDEO_RATINGS_REL);
    }

    /**
     * Returns the link element relating to video complaints.
     *
     * @return Zend_Gdata_App_Extension_Link
     */
    public function getVideoComplaintsLink()
    {
        return $this->getLink(Zend_Gdata_YouTube::VIDEO_COMPLAINTS_REL);
    }

    /**
     * Gets the YouTube video ID based upon the atom:id value
     *
     * @return string The video ID
     */
    public function getVideoId()
    {
        if ($this->getMajorProtocolVersion() == 2) {
            $videoId = $this->getMediaGroup()->getVideoId()->text;
        } else {
            $fullId = $this->getId()->getText();
            $position = strrpos($fullId, '/');
            if ($position === false) {
                #require_once 'Zend/Gdata/App/Exception.php';
                throw new Zend_Gdata_App_Exception(
                    'Slash not found in atom:id of ' . $fullId);
            } else {
                $videoId = substr($fullId, $position + 1);
            }
        }
        return $videoId;
    }

    /**
     * Gets the date that the video was recorded.
     *
     * @return string|null The date that the video was recorded
     */
    public function getVideoRecorded()
    {
        $recorded = $this->getRecorded();
        if ($recorded != null) {
          return $recorded->getText();
        } else {
          return null;
        }
    }

    /**
     * Sets the date that the video was recorded.
     *
     * @param string $recorded The date that the video was recorded, in the
     *          format of '2001-06-19'
     */
    public function setVideoRecorded($recorded)
    {
        $this->setRecorded(
            new Zend_Gdata_YouTube_Extension_Recorded($recorded));
        return $this;
    }

    /**
     * Gets the georss:where element
     *
     * @return Zend_Gdata_Geo_Extension_GeoRssWhere
     */
    public function getWhere()
    {
        return $this->_where;
    }

    /**
     * Sets the georss:where element
     *
     * @param Zend_Gdata_Geo_Extension_GeoRssWhere $value The georss:where class value
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setWhere($value)
    {
        $this->_where = $value;
        return $this;
    }

    /**
     * Gets the title of the video as a string.  null is returned
     * if the video title is not available.
     *
     * @return string|null The title of the video
     */
    public function getVideoTitle()
    {
        $this->ensureMediaGroupIsNotNull();
        if ($this->getMediaGroup()->getTitle() != null) {
            return $this->getMediaGroup()->getTitle()->getText();
        } else {
            return null;
        }
    }

    /**
     * Sets the title of the video as a string.
     *
     * @param string $title Title for the video
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setVideoTitle($title)
    {
        $this->ensureMediaGroupIsNotNull();
        $this->getMediaGroup()->setTitle(
            new Zend_Gdata_Media_Extension_MediaTitle($title));
        return $this;
    }

    /**
     * Sets the description of the video as a string.
     *
     * @param string $description Description for the video
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setVideoDescription($description)
    {
        $this->ensureMediaGroupIsNotNull();
        $this->getMediaGroup()->setDescription(
            new Zend_Gdata_Media_Extension_MediaDescription($description));
        return $this;
    }


    /**
     * Gets the description  of the video as a string.  null is returned
     * if the video description is not available.
     *
     * @return string|null The description of the video
     */
    public function getVideoDescription()
    {
        $this->ensureMediaGroupIsNotNull();
        if ($this->getMediaGroup()->getDescription() != null) {
            return $this->getMediaGroup()->getDescription()->getText();
        } else {
            return null;
        }
    }

    /**
     * Gets the URL of the YouTube video watch page.  null is returned
     * if the video watch page URL is not available.
     *
     * @return string|null The URL of the YouTube video watch page
     */
    public function getVideoWatchPageUrl()
    {
        $this->ensureMediaGroupIsNotNull();
        if ($this->getMediaGroup()->getPlayer() != null &&
             array_key_exists(0, $this->getMediaGroup()->getPlayer())) {
            $players = $this->getMediaGroup()->getPlayer();
            return $players[0]->getUrl();
        } else {
            return null;
        }
    }

    /**
     * Gets an array of the thumbnails representing the video.
     * Each thumbnail is an element of the array, and is an
     * array of the thumbnail properties - time, height, width,
     * and url.  For convient usage inside a foreach loop, an
     * empty array is returned if there are no thumbnails.
     *
     * @return array An array of video thumbnails.
     */
    public function getVideoThumbnails()
    {
        $this->ensureMediaGroupIsNotNull();
        if ($this->getMediaGroup()->getThumbnail() != null) {

            $thumbnailArray = array();

            foreach ($this->getMediaGroup()->getThumbnail() as $thumbnailObj) {
                $thumbnail = array();
                $thumbnail['time'] = $thumbnailObj->time;
                $thumbnail['height'] = $thumbnailObj->height;
                $thumbnail['width'] = $thumbnailObj->width;
                $thumbnail['url'] = $thumbnailObj->url;
                $thumbnailArray[] = $thumbnail;
            }
            return $thumbnailArray;
        } else {
            return array();
        }
    }

    /**
     * Gets the URL of the flash player SWF.  null is returned if the
     * duration value is not available.
     *
     * @return string|null The URL of the flash player SWF
     */
    public function getFlashPlayerUrl()
    {
        $this->ensureMediaGroupIsNotNull();
        foreach ($this->getMediaGroup()->getContent() as $content) {
                if ($content->getType() === 'application/x-shockwave-flash') {
                    return $content->getUrl();
                }
            }
        return null;
    }

    /**
     * Gets the duration of the video, in seconds.  null is returned
     * if the duration value is not available.
     *
     * @return string|null The duration of the video, in seconds.
     */
    public function getVideoDuration()
    {
        $this->ensureMediaGroupIsNotNull();
        if ($this->getMediaGroup()->getDuration() != null) {
            return $this->getMediaGroup()->getDuration()->getSeconds();
        } else {
            return null;
        }
    }

    /**
     * Checks whether the video is private.
     *
     * @return bool Return true if video is private
     */
    public function isVideoPrivate()
    {
        $this->ensureMediaGroupIsNotNull();
        if ($this->getMediaGroup()->getPrivate() != null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sets video to private.
     *
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setVideoPrivate()
    {
        $this->ensureMediaGroupIsNotNull();
        $this->getMediaGroup()->setPrivate(new Zend_Gdata_YouTube_Extension_Private());
        return $this;
    }

    /**
     * Sets a private video to be public.
     *
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setVideoPublic()
    {
        $this->ensureMediaGroupIsNotNull();
        $this->getMediaGroup()->private = null;
        return $this;
    }

    /**
     * Gets an array of the tags assigned to this video.  For convient
     * usage inside a foreach loop, an empty array is returned when there
     * are no tags assigned.
     *
     * @return array An array of the tags assigned to this video
     */
    public function getVideoTags()
    {
        $this->ensureMediaGroupIsNotNull();
        if ($this->getMediaGroup()->getKeywords() != null) {

            $keywords = $this->getMediaGroup()->getKeywords();
            $keywordsString = $keywords->getText();
            if (strlen(trim($keywordsString)) > 0) {
                return preg_split('/(, *)|,/', $keywordsString);
            }
        }
        return array();
    }

    /**
     * Sets the keyword tags for a video.
     *
     * @param mixed $tags Either a comma-separated string or an array
     * of tags for the video
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setVideoTags($tags)
    {
        $this->ensureMediaGroupIsNotNull();
        $keywords = new Zend_Gdata_Media_Extension_MediaKeywords();
        if (is_array($tags)) {
            $tags = implode(', ', $tags);
        }
        $keywords->setText($tags);
        $this->getMediaGroup()->setKeywords($keywords);
        return $this;
    }

    /**
     * Gets the number of views for this video.  null is returned if the
     * number of views is not available.
     *
     * @return string|null The number of views for this video
     */
    public function getVideoViewCount()
    {
        if ($this->getStatistics() != null) {
            return $this->getStatistics()->getViewCount();
        } else {
            return null;
        }
    }

    /**
     * Gets the location specified for this video, if available.  The location
     * is returned as an array containing the keys 'longitude' and 'latitude'.
     * null is returned if the location is not available.
     *
     * @return array|null The location specified for this video
     */
    public function getVideoGeoLocation()
    {
        if ($this->getWhere() != null &&
            $this->getWhere()->getPoint() != null &&
            ($position = $this->getWhere()->getPoint()->getPos()) != null) {

            $positionString = $position->__toString();

            if (strlen(trim($positionString)) > 0) {
                $positionArray = explode(' ', trim($positionString));
                if (count($positionArray) == 2) {
                    $returnArray = array();
                    $returnArray['latitude'] = $positionArray[0];
                    $returnArray['longitude'] = $positionArray[1];
                    return $returnArray;
                }
            }
        }
        return null;
    }

    /**
     * Gets the rating information for this video, if available.  The rating
     * is returned as an array containing the keys 'average' and 'numRaters'.
     * null is returned if the rating information is not available.
     *
     * @return array|null The rating information for this video
     */
    public function getVideoRatingInfo()
    {
        if ($this->getRating() != null) {
            $returnArray = array();
            $returnArray['average'] = $this->getRating()->getAverage();
            $returnArray['numRaters'] = $this->getRating()->getNumRaters();
            return $returnArray;
        } else {
            return null;
        }
    }

    /**
     * Gets the category of this video, if available.  The category is returned
     * as a string. Valid categories are found at:
     * http://gdata.youtube.com/schemas/2007/categories.cat
     * If the category is not set, null is returned.
     *
     * @return string|null The category of this video
     */
    public function getVideoCategory()
    {
        $this->ensureMediaGroupIsNotNull();
        $categories = $this->getMediaGroup()->getCategory();
        if ($categories != null) {
            foreach($categories as $category) {
                if ($category->getScheme() == self::YOUTUBE_CATEGORY_SCHEMA) {
                    return $category->getText();
                }
            }
        }
        return null;
    }

    /**
     * Sets the category of the video as a string.
     *
     * @param string $category Categories for the video
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setVideoCategory($category)
    {
        $this->ensureMediaGroupIsNotNull();
        $this->getMediaGroup()->setCategory(array(new Zend_Gdata_Media_Extension_MediaCategory($category, self::YOUTUBE_CATEGORY_SCHEMA)));
        return $this;
    }

    /**
     * Gets the developer tags for the video, if available and if client is
     * authenticated with a valid developerKey. The tags are returned
     * as an array.
     * If no tags are set, null is returned.
     *
     * @return array|null The developer tags for this video or null if none were set.
     */
    public function getVideoDeveloperTags()
    {
        $developerTags = null;
        $this->ensureMediaGroupIsNotNull();

        $categoryArray = $this->getMediaGroup()->getCategory();
        if ($categoryArray != null) {
            foreach ($categoryArray as $category) {
                if ($category instanceof Zend_Gdata_Media_Extension_MediaCategory) {
                    if ($category->getScheme() == self::YOUTUBE_DEVELOPER_TAGS_SCHEMA) {
                        $developerTags[] = $category->getText();
                    }
                }
            }
            return $developerTags;
        }
        return null;
    }

    /**
     * Adds a developer tag to array of tags for the video.
     *
     * @param string $developerTag DeveloperTag for the video
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function addVideoDeveloperTag($developerTag)
    {
        $this->ensureMediaGroupIsNotNull();
        $newCategory = new Zend_Gdata_Media_Extension_MediaCategory($developerTag, self::YOUTUBE_DEVELOPER_TAGS_SCHEMA);

        if ($this->getMediaGroup()->getCategory() == null) {
            $this->getMediaGroup()->setCategory($newCategory);
        } else {
            $categories = $this->getMediaGroup()->getCategory();
            $categories[] = $newCategory;
            $this->getMediaGroup()->setCategory($categories);
        }
        return $this;
    }

    /**
     * Set multiple developer tags for the video as strings.
     *
     * @param array $developerTags Array of developerTag for the video
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface
     */
    public function setVideoDeveloperTags($developerTags)
    {
        foreach($developerTags as $developerTag) {
            $this->addVideoDeveloperTag($developerTag);
        }
        return $this;
    }


    /**
     * Get the current publishing state of the video.
     *
     * @return Zend_Gdata_YouTube_Extension_State|null The publishing state of this video
     */
    public function getVideoState()
    {
        $control = $this->getControl();
        if ($control != null &&
            $control->getDraft() != null &&
            $control->getDraft()->getText() == 'yes') {

            return $control->getState();
        }
        return null;
    }

    /**
     * Get the VideoEntry's Zend_Gdata_YouTube_Extension_MediaGroup object.
     * If the mediaGroup does not exist, then set it.
     *
     * @return void
     */
    public function ensureMediaGroupIsNotNull()
    {
        if ($this->getMediagroup() == null) {
            $this->setMediagroup(new Zend_Gdata_YouTube_Extension_MediaGroup());
        }
    }

    /**
     * Helper function to conveniently set a video's rating.
     *
     * @param integer $ratingValue A number representing the rating. Must
     *          be between 1 and 5 inclusive.
     * @throws Zend_Gdata_Exception
     * @return Zend_Gdata_YouTube_VideoEntry Provides a fluent interface.
     */
    public function setVideoRating($ratingValue)
    {
        if ($ratingValue < 1 || $ratingValue > 5) {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                'Rating for video entry must be between 1 and 5 inclusive.');
        }

         #require_once 'Zend/Gdata/Extension/Rating.php';
         $rating = new Zend_Gdata_Extension_Rating(null, 1, 5, null,
            $ratingValue);
        $this->setRating($rating);
        return $this;
    }

    /**
     * Retrieve the URL for a video's comment feed.
     *
     * @return string|null The URL if found, or null if not found.
     */
    public function getVideoCommentFeedUrl()
    {
        $commentsExtension = $this->getComments();
        $commentsFeedUrl = null;
        if ($commentsExtension) {
            $commentsFeedLink = $commentsExtension->getFeedLink();
            if ($commentsFeedLink) {
                $commentsFeedUrl = $commentsFeedLink->getHref();
            }
        }
        return $commentsFeedUrl;
    }

}
