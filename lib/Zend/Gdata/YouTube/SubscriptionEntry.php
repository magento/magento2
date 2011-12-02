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
 * @version    $Id: SubscriptionEntry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Entry
 */
#require_once 'Zend/Gdata/Entry.php';

/**
 * @see Zend_Gdata_Extension_FeedLink
 */
#require_once 'Zend/Gdata/Extension/FeedLink.php';

/**
 * @see Zend_Gdata_YouTube_Extension_Description
 */
#require_once 'Zend/Gdata/YouTube/Extension/Description.php';

/**
 * @see Zend_Gdata_YouTube_Extension_PlaylistTitle
 */
#require_once 'Zend/Gdata/YouTube/Extension/PlaylistTitle.php';

/**
 * @see Zend_Gdata_YouTube_Extension_PlaylistId
 */
#require_once 'Zend/Gdata/YouTube/Extension/PlaylistId.php';

/**
 * @see Zend_Gdata_Media_Extension_MediaThumbnail
 */
#require_once 'Zend/Gdata/Media/Extension/MediaThumbnail.php';

/**
 * @see Zend_Gdata_YouTube_Extension_Username
 */
#require_once 'Zend/Gdata/YouTube/Extension/Username.php';

/**
 * @see Zend_Gdata_YouTube_Extension_CountHint
 */
#require_once 'Zend/Gdata/YouTube/Extension/CountHint.php';

/**
 * @see Zend_Gdata_YouTube_Extension_QueryString
 */
#require_once 'Zend/Gdata/YouTube/Extension/QueryString.php';

/**
 * Represents the YouTube video subscription flavor of an Atom entry
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_YouTube_SubscriptionEntry extends Zend_Gdata_Entry
{

    protected $_entryClassName = 'Zend_Gdata_YouTube_SubscriptionEntry';

    /**
     * Nested feed links
     *
     * @var array
     */
    protected $_feedLink = array();

    /**
     * The username of this entry.
     *
     * @var Zend_Gdata_YouTube_Extension_Username
     */
    protected $_username = null;

    /**
     * The playlist title for this entry.
     *
     * This element is only used on subscriptions to playlists.
     *
     * @var Zend_Gdata_YouTube_Extension_PlaylistTitle
     */
    protected $_playlistTitle = null;

    /**
     * The playlist id for this entry.
     *
     * This element is only used on subscriptions to playlists.
     *
     * @var Zend_Gdata_YouTube_Extension_PlaylistId
     */
    protected $_playlistId = null;

    /**
     * The media:thumbnail element for this entry.
     *
     * This element is only used on subscriptions to playlists.
     *
     * @var Zend_Gdata_Media_Extension_MediaThumbnail
     */
    protected $_mediaThumbnail = null;

    /**
     * The countHint for this entry.
     *
     * @var Zend_Gdata_YouTube_Extension_CountHint
     */
    protected $_countHint = null;

    /**
     * The queryString for this entry.
     *
     * @var Zend_Gdata_YouTube_Extension_QueryString
     */
    protected $_queryString = null;

    /**
     * Creates a subscription entry, representing an individual subscription
     * in a list of subscriptions, usually associated with an individual user.
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
        if ($this->_countHint != null) {
            $element->appendChild($this->_countHint->getDOM($element->ownerDocument));
        }
        if ($this->_playlistTitle != null) {
            $element->appendChild($this->_playlistTitle->getDOM($element->ownerDocument));
        }
        if ($this->_playlistId != null) {
            $element->appendChild($this->_playlistId->getDOM($element->ownerDocument));
        }
        if ($this->_mediaThumbnail != null) {
            $element->appendChild($this->_mediaThumbnail->getDOM($element->ownerDocument));
        }
        if ($this->_username != null) {
            $element->appendChild($this->_username->getDOM($element->ownerDocument));
        }
        if ($this->_queryString != null) {
            $element->appendChild($this->_queryString->getDOM($element->ownerDocument));
        }
        if ($this->_feedLink != null) {
            foreach ($this->_feedLink as $feedLink) {
                $element->appendChild($feedLink->getDOM($element->ownerDocument));
            }
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
        case $this->lookupNamespace('gd') . ':' . 'feedLink':
            $feedLink = new Zend_Gdata_Extension_FeedLink();
            $feedLink->transferFromDOM($child);
            $this->_feedLink[] = $feedLink;
            break;
        case $this->lookupNamespace('media') . ':' . 'thumbnail':
            $mediaThumbnail = new Zend_Gdata_Media_Extension_MediaThumbnail();
            $mediaThumbnail->transferFromDOM($child);
            $this->_mediaThumbnail = $mediaThumbnail;
            break;
        case $this->lookupNamespace('yt') . ':' . 'countHint':
            $countHint = new Zend_Gdata_YouTube_Extension_CountHint();
            $countHint->transferFromDOM($child);
            $this->_countHint = $countHint;
            break;
        case $this->lookupNamespace('yt') . ':' . 'playlistTitle':
            $playlistTitle = new Zend_Gdata_YouTube_Extension_PlaylistTitle();
            $playlistTitle->transferFromDOM($child);
            $this->_playlistTitle = $playlistTitle;
            break;
        case $this->lookupNamespace('yt') . ':' . 'playlistId':
            $playlistId = new Zend_Gdata_YouTube_Extension_PlaylistId();
            $playlistId->transferFromDOM($child);
            $this->_playlistId = $playlistId;
            break;
        case $this->lookupNamespace('yt') . ':' . 'queryString':
            $queryString = new Zend_Gdata_YouTube_Extension_QueryString();
            $queryString->transferFromDOM($child);
            $this->_queryString = $queryString;
            break;
        case $this->lookupNamespace('yt') . ':' . 'username':
            $username = new Zend_Gdata_YouTube_Extension_Username();
            $username->transferFromDOM($child);
            $this->_username = $username;
            break;
        default:
            parent::takeChildFromDOM($child);
            break;
        }
    }

    /**
     * Sets the array of embedded feeds related to the video
     *
     * @param array $feedLink The array of embedded feeds relating to the video
     * @return Zend_Gdata_YouTube_SubscriptionEntry Provides a fluent interface
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
     * Get the playlist title for a 'playlist' subscription.
     *
     * @throws Zend_Gdata_App_VersionException
     * @return Zend_Gdata_YouTube_Extension_PlaylistId
     */
    public function getPlaylistId()
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            #require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The getPlaylistId ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            return $this->_playlistId;
        }
    }

    /**
     * Sets the yt:playlistId element for a new playlist subscription.
     *
     * @param Zend_Gdata_YouTube_Extension_PlaylistId $id The id of
     *        the playlist to which to subscribe to.
     * @throws Zend_Gdata_App_VersionException
     * @return Zend_Gdata_YouTube_SubscriptionEntry Provides a fluent interface
     */
    public function setPlaylistId($id = null)
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            #require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The setPlaylistTitle ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            $this->_playlistId = $id;
            return $this;
        }
    }

    /**
     * Get the queryString of the subscription
     *
     * @return Zend_Gdata_YouTube_Extension_QueryString
     */
    public function getQueryString()
    {
        return $this->_queryString;
    }

    /**
     * Sets the yt:queryString element for a new keyword subscription.
     *
     * @param Zend_Gdata_YouTube_Extension_QueryString $queryString The query
     *        string to subscribe to
     * @return Zend_Gdata_YouTube_SubscriptionEntry Provides a fluent interface
     */
    public function setQueryString($queryString = null)
    {
        $this->_queryString = $queryString;
        return $this;
    }

    /**
     * Get the playlist title for a 'playlist' subscription.
     *
     * @throws Zend_Gdata_App_VersionException
     * @return Zend_Gdata_YouTube_Extension_PlaylistTitle
     */
    public function getPlaylistTitle()
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            #require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The getPlaylistTitle ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            return $this->_playlistTitle;
        }
    }

    /**
     * Sets the yt:playlistTitle element for a new playlist subscription.
     *
     * @param Zend_Gdata_YouTube_Extension_PlaylistTitle $title The title of
     *        the playlist to which to subscribe to.
     * @throws Zend_Gdata_App_VersionException
     * @return Zend_Gdata_YouTube_SubscriptionEntry Provides a fluent interface
     */
    public function setPlaylistTitle($title = null)
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            #require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The setPlaylistTitle ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            $this->_playlistTitle = $title;
            return $this;
        }
    }

    /**
     * Get the counthint for a subscription.
     *
     * @throws Zend_Gdata_App_VersionException
     * @return Zend_Gdata_YouTube_Extension_CountHint
     */
    public function getCountHint()
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            #require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The getCountHint ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            return $this->_countHint;
        }
    }

    /**
     * Get the thumbnail for a subscription.
     *
     * @throws Zend_Gdata_App_VersionException
     * @return Zend_Gdata_Media_Extension_MediaThumbnail
     */
    public function getMediaThumbnail()
    {
        if (($this->getMajorProtocolVersion() == null) ||
           ($this->getMajorProtocolVersion() == 1)) {
            #require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('The getMediaThumbnail ' .
                ' method is only supported as of version 2 of the YouTube ' .
                'API.');
        } else {
            return $this->_mediaThumbnail;
        }
    }

    /**
     * Get the username for a channel subscription.
     *
     * @return Zend_Gdata_YouTube_Extension_Username
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * Sets the username for a new channel subscription.
     *
     * @param Zend_Gdata_YouTube_Extension_Username $username The username of
     *        the channel to which to subscribe to.
     * @return Zend_Gdata_YouTube_SubscriptionEntry Provides a fluent interface
     */
    public function setUsername($username = null)
    {
        $this->_username = $username;
        return $this;
    }

}
