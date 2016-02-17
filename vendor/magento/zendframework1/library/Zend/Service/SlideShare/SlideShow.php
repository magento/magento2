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
 * @package    Zend_Service
 * @subpackage SlideShare
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * The Zend_Service_SlideShare_SlideShow class represents a slide show on the
 * slideshare.net servers.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage SlideShare
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_SlideShare_SlideShow
{
    /**
     * Status constant mapping for web service
     *
     */
    const STATUS_QUEUED = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_READY = 2;
    const STATUS_FAILED = 3;

    /**
     * The HTML code to embed the slide show in a web page
     *
     * @var string the HTML to embed the slide show
     */
    protected $_embedCode;

    /**
     * The URI for the thumbnail representation of the slide show
     *
     * @var string The URI of a thumbnail image
     */
    protected $_thumbnailUrl;

    /**
     * The title of the slide show
     *
     * @var string The slide show title
     */
    protected $_title;

    /**
     * The Description of the slide show
     *
     * @var string The slide show description
     */
    protected $_description;

    /**
     * The status of the silde show on the server
     *
     * @var int The Slide show status code
     */
    protected $_status;

    /**
     * The Description of the slide show status code
     *
     * @var string The status description
     */
    protected $_statusDescription;

    /**
     * The URL for the slide show
     *
     * @var string the URL for the slide show
     */
    protected $_url;

    /**
     * The number of views this slide show has received
     *
     * @var int the number of views
     */
    protected $_numViews;

    /**
     * The ID of the slide show on the server
     *
     * @var int the Slide show ID number on the server
     */
    protected $_slideShowId;

    /**
     * A slide show filename on the local filesystem (when uploading)
     *
     * @var string the local filesystem path & file of the slide show to upload
     */
    protected $_slideShowFilename;

    /**
     * An array of tags associated with the slide show
     *
     * @var array An array of tags associated with the slide show
     */
    protected $_tags = array();

    /**
     * The location of the slide show
     *
     * @var string the Location
     */
    protected $_location;

    /**
     * The transcript associated with the slide show
     *
     * @var string the Transscript
     */
    protected $_transcript;


    /**
     * Retrieves the location of the slide show
     *
     * @return string the Location
     */
    public function getLocation()
    {
        return $this->_location;
    }

    /**
     * Sets the location of the slide show
     *
     * @param string $loc The location to use
     * @return Zend_Service_SlideShare_SlideShow
     */
    public function setLocation($loc)
    {
        $this->_location = (string)$loc;
        return $this;
    }

    /**
     * Gets the transcript for this slide show
     *
     * @return string the Transcript
     */
    public function getTranscript()
    {
        return $this->_transcript;
    }

    /**
     * Sets the transcript for this slide show
     *
     * @param string $t The transcript
     * @return Zend_Service_SlideShare_SlideShow
     */
    public function setTranscript($t)
    {
        $this->_transcript = (string)$t;
        return $this;
    }

    /**
     * Adds a tag to the slide show
     *
     * @param string $tag The tag to add
     * @return Zend_Service_SlideShare_SlideShow
     */
    public function addTag($tag)
    {
        $this->_tags[] = (string)$tag;
        return $this;
    }

    /**
     * Sets the tags for the slide show
     *
     * @param array $tags An array of tags to set
     * @return Zend_Service_SlideShare_SlideShow
     */
    public function setTags(Array $tags)
    {
        $this->_tags = $tags;
        return $this;
    }

    /**
     * Gets all of the tags associated with the slide show
     *
     * @return array An array of tags for the slide show
     */
    public function getTags()
    {
        return $this->_tags;
    }

    /**
     * Sets the filename on the local filesystem of the slide show
     * (for uploading a new slide show)
     *
     * @param string $file The full path & filename to the slide show
     * @return Zend_Service_SlideShare_SlideShow
     */
    public function setFilename($file)
    {
        $this->_slideShowFilename = (string)$file;
        return $this;
    }

    /**
     * Retrieves the filename on the local filesystem of the slide show
     * which will be uploaded
     *
     * @return string The full path & filename to the slide show
     */
    public function getFilename()
    {
        return $this->_slideShowFilename;
    }

    /**
     * Sets the ID for the slide show
     *
     * @param int $id The slide show ID
     * @return Zend_Service_SlideShare_SlideShow
     */
    public function setId($id)
    {
        $this->_slideShowId = (string)$id;
        return $this;
    }

    /**
     * Gets the ID for the slide show
     *
     * @return int The slide show ID
     */
    public function getId()
    {
        return $this->_slideShowId;
    }

    /**
     * Sets the HTML embed code for the slide show
     *
     * @param string $code The HTML embed code
     * @return Zend_Service_SlideShare_SlideShow
     */
    public function setEmbedCode($code)
    {
        $this->_embedCode = (string)$code;
        return $this;
    }

    /**
     * Retrieves the HTML embed code for the slide show
     *
     * @return string the HTML embed code
     */
    public function getEmbedCode()
    {
        return $this->_embedCode;
    }

    /**
     * Sets the Thumbnail URI for the slide show
     *
     * @param string $url The URI for the thumbnail image
     * @return Zend_Service_SlideShare_SlideShow
     */
    public function setThumbnailUrl($url)
    {
        $this->_thumbnailUrl = (string) $url;
        return $this;
    }

    /**
     * Retrieves the Thumbnail URi for the slide show
     *
     * @return string The URI for the thumbnail image
     */
    public function getThumbnailUrl()
    {
        return $this->_thumbnailUrl;
    }

    /**
     * Sets the title for the Slide show
     *
     * @param string $title The slide show title
     * @return Zend_Service_SlideShare_SlideShow
     */
    public function setTitle($title)
    {
        $this->_title = (string)$title;
        return $this;
    }

    /**
     * Retrieves the Slide show title
     *
     * @return string the Slide show title
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Sets the description for the Slide show
     *
     * @param string $desc The description of the slide show
     * @return Zend_Service_SlideShare_SlideShow
     */
    public function setDescription($desc)
    {
        $this->_description = (string)$desc;
        return $this;
    }

    /**
     * Gets the description of the slide show
     *
     * @return string The slide show description
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Sets the numeric status of the slide show on the server
     *
     * @param int $status The numeric status on the server
     * @return Zend_Service_SlideShare_SlideShow
     */
    public function setStatus($status)
    {
        $this->_status = (int)$status;
        return $this;
    }

    /**
     * Gets the numeric status of the slide show on the server
     *
     * @return int A Zend_Service_SlideShare_SlideShow Status constant
     */
    public function getStatus()
    {
        return $this->_status;
    }

    /**
     * Sets the textual description of the status of the slide show on the server
     *
     * @param string $desc The textual description of the status of the slide show
     * @return Zend_Service_SlideShare_SlideShow
     */
    public function setStatusDescription($desc)
    {
        $this->_statusDescription = (string)$desc;
        return $this;
    }

    /**
     * Gets the textual description of the status of the slide show on the server
     *
     * @return string the textual description of the service
     */
    public function getStatusDescription()
    {
        return $this->_statusDescription;
    }

    /**
     * Sets the permanent link of the slide show
     *
     * @see Zend_Service_SlideShare_SlideShow::setUrl()
     *
     * @param string $url The permanent URL for the slide show
     * @return Zend_Service_SlideShare_SlideShow
     * @deprecated Since 1.12.10, use setUrl()
     */
    public function setPermaLink($url)
    {
        $this->setUrl($url);
        return $this;
    }

    /**
     * Gets the permanent link of the slide show
     *
     * @see Zend_Service_SlideShare_SlideShow::getUrl()
     *
     * @return string the permanent URL for the slide show
     * @deprecated Since 1.12.10, use getUrl()
     */
    public function getPermaLink()
    {
        return $this->getUrl();
    }

    /**
     * Sets the URL of the slide show
     *
     * @param  string $url The URL for the slide show
     * @return self
     */
    public function setUrl($url)
    {
        $this->_url = (string)$url;
        return $this;
    }

    /**
     * Gets the URL of the slide show
     *
     * @return string The URL for the slide show
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Sets the number of views the slide show has received
     *
     * @param int $views The number of views
     * @return Zend_Service_SlideShare_SlideShow
     */
    public function setNumViews($views)
    {
        $this->_numViews = (int)$views;
        return $this;
    }

    /**
     * Gets the number of views the slide show has received
     *
     * @return int The number of views
     */
    public function getNumViews()
    {
        return $this->_numViews;
    }
}
