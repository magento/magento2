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
 * @see Zend_Gdata_Media
 */
#require_once 'Zend/Gdata/Media.php';

/**
 * @see Zend_Gdata_YouTube_VideoEntry
 */
#require_once 'Zend/Gdata/YouTube/VideoEntry.php';

/**
 * @see Zend_Gdata_YouTube_VideoFeed
 */
#require_once 'Zend/Gdata/YouTube/VideoFeed.php';

/**
 * @see Zend_Gdata_YouTube_CommentFeed
 */
#require_once 'Zend/Gdata/YouTube/CommentFeed.php';

/**
 * @see Zend_Gdata_YouTube_PlaylistListFeed
 */
#require_once 'Zend/Gdata/YouTube/PlaylistListFeed.php';

/**
 * @see Zend_Gdata_YouTube_SubscriptionFeed
 */
#require_once 'Zend/Gdata/YouTube/SubscriptionFeed.php';

/**
 * @see Zend_Gdata_YouTube_ContactFeed
 */
#require_once 'Zend/Gdata/YouTube/ContactFeed.php';

/**
 * @see Zend_Gdata_YouTube_PlaylistVideoFeed
 */
#require_once 'Zend/Gdata/YouTube/PlaylistVideoFeed.php';

/**
 * @see Zend_Gdata_YouTube_ActivityFeed
 */
#require_once 'Zend/Gdata/YouTube/ActivityFeed.php';

/**
 * @see Zend_Gdata_YouTube_InboxFeed
 */
#require_once 'Zend/Gdata/YouTube/InboxFeed.php';

/** @see Zend_Xml_Security */
#require_once 'Zend/Xml/Security.php';

/**
 * Service class for interacting with the YouTube Data API.
 * @link http://code.google.com/apis/youtube/
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_YouTube extends Zend_Gdata_Media
{

    const AUTH_SERVICE_NAME = 'youtube';
    const CLIENTLOGIN_URL = 'https://www.google.com/youtube/accounts/ClientLogin';

    const STANDARD_TOP_RATED_URI = 'https://gdata.youtube.com/feeds/api/standardfeeds/top_rated';
    const STANDARD_MOST_VIEWED_URI = 'https://gdata.youtube.com/feeds/api/standardfeeds/most_viewed';
    const STANDARD_RECENTLY_FEATURED_URI = 'https://gdata.youtube.com/feeds/api/standardfeeds/recently_featured';
    const STANDARD_WATCH_ON_MOBILE_URI = 'https://gdata.youtube.com/feeds/api/standardfeeds/watch_on_mobile';

    const STANDARD_TOP_RATED_URI_V2 =
        'https://gdata.youtube.com/feeds/api/standardfeeds/top_rated';
    const STANDARD_MOST_VIEWED_URI_V2 =
        'https://gdata.youtube.com/feeds/api/standardfeeds/most_viewed';
    const STANDARD_RECENTLY_FEATURED_URI_V2 =
        'https://gdata.youtube.com/feeds/api/standardfeeds/recently_featured';
    const STANDARD_WATCH_ON_MOBILE_URI_V2 =
        'https://gdata.youtube.com/feeds/api/standardfeeds/watch_on_mobile';

    const USER_URI = 'https://gdata.youtube.com/feeds/api/users';
    const VIDEO_URI = 'https://gdata.youtube.com/feeds/api/videos';
    const PLAYLIST_REL = 'http://gdata.youtube.com/schemas/2007#playlist';
    const USER_UPLOADS_REL = 'http://gdata.youtube.com/schemas/2007#user.uploads';
    const USER_PLAYLISTS_REL = 'http://gdata.youtube.com/schemas/2007#user.playlists';
    const USER_SUBSCRIPTIONS_REL = 'http://gdata.youtube.com/schemas/2007#user.subscriptions';
    const USER_CONTACTS_REL = 'http://gdata.youtube.com/schemas/2007#user.contacts';
    const USER_FAVORITES_REL = 'http://gdata.youtube.com/schemas/2007#user.favorites';
    const VIDEO_RESPONSES_REL = 'http://gdata.youtube.com/schemas/2007#video.responses';
    const VIDEO_RATINGS_REL = 'http://gdata.youtube.com/schemas/2007#video.ratings';
    const VIDEO_COMPLAINTS_REL = 'http://gdata.youtube.com/schemas/2007#video.complaints';
    const ACTIVITY_FEED_URI = 'https://gdata.youtube.com/feeds/api/events';
    const FRIEND_ACTIVITY_FEED_URI =
        'https://gdata.youtube.com/feeds/api/users/default/friendsactivity';

    /**
     * The URI of the in-reply-to schema for comments in reply to
     * other comments.
     *
     * @var string
     */
     const IN_REPLY_TO_SCHEME =
         'http://gdata.youtube.com/schemas/2007#in-reply-to';

    /**
     * The URI of the inbox feed for the currently authenticated user.
     *
     * @var string
     */
    const INBOX_FEED_URI =
        'https://gdata.youtube.com/feeds/api/users/default/inbox';

    /**
     * The maximum number of users for which activity can be requested for,
     * as enforced by the API.
     *
     * @var integer
     */
    const ACTIVITY_FEED_MAX_USERS = 20;

    /**
     * The suffix for a feed of favorites.
     *
     * @var string
     */
    const FAVORITES_URI_SUFFIX = 'favorites';

    /**
     * The suffix for the user's upload feed.
     *
     * @var string
     */
    const UPLOADS_URI_SUFFIX = 'uploads';

    /**
     * The suffix for a feed of video responses.
     *
     * @var string
     */
    const RESPONSES_URI_SUFFIX = 'responses';

    /**
     * The suffix for a feed of related videos.
     *
     * @var string
     */
    const RELATED_URI_SUFFIX = 'related';

    /**
     * The suffix for a feed of messages (inbox entries).
     *
     * @var string
     */
    const INBOX_URI_SUFFIX = 'inbox';

    /**
     * Namespaces used for Zend_Gdata_YouTube
     *
     * @var array
     */
    public static $namespaces = array(
        array('yt', 'http://gdata.youtube.com/schemas/2007', 1, 0),
        array('georss', 'http://www.georss.org/georss', 1, 0),
        array('gml', 'http://www.opengis.net/gml', 1, 0),
        array('media', 'http://search.yahoo.com/mrss/', 1, 0)
    );

    /**
     * Create Zend_Gdata_YouTube object
     *
     * @param Zend_Http_Client $client (optional) The HTTP client to use when
     *          when communicating with the Google servers.
     * @param string $applicationId The identity of the app in the form of
     *        Company-AppName-Version
     * @param string $clientId The clientId issued by the YouTube dashboard
     * @param string $developerKey The developerKey issued by the YouTube dashboard
     */
    public function __construct($client = null,
        $applicationId = 'MyCompany-MyApp-1.0', $clientId = null,
        $developerKey = null)
    {
        $this->registerPackage('Zend_Gdata_YouTube');
        $this->registerPackage('Zend_Gdata_YouTube_Extension');
        $this->registerPackage('Zend_Gdata_Media');
        $this->registerPackage('Zend_Gdata_Media_Extension');

        // NOTE This constructor no longer calls the parent constructor
        $this->setHttpClient($client, $applicationId, $clientId, $developerKey);
    }

    /**
     * Set the Zend_Http_Client object used for communication
     *
     * @param Zend_Http_Client $client The client to use for communication
     * @throws Zend_Gdata_App_HttpException
     * @return Zend_Gdata_App Provides a fluent interface
     */
    public function setHttpClient($client,
        $applicationId = 'MyCompany-MyApp-1.0', $clientId = null,
        $developerKey = null)
    {
        if ($client === null) {
            $client = new Zend_Http_Client();
        }
        if (!$client instanceof Zend_Http_Client) {
            #require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException(
                'Argument is not an instance of Zend_Http_Client.');
        }

        if ($clientId != null) {
            $client->setHeaders('X-GData-Client', $clientId);
        }

        if ($developerKey != null) {
            $client->setHeaders('X-GData-Key', 'key='. $developerKey);
        }

        return parent::setHttpClient($client, $applicationId);
    }

    /**
     * Retrieves a feed of videos.
     *
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined
     * @return Zend_Gdata_YouTube_VideoFeed The feed of videos found at the
     *         specified URL.
     */
    public function getVideoFeed($location = null)
    {
        if ($location == null) {
            $uri = self::VIDEO_URI;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    /**
     * Retrieves a specific video entry.
     *
     * @param mixed $videoId The ID of the video to retrieve.
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined.
     * @param boolean $fullEntry (optional) Retrieve the full metadata for the
     *         entry. Only possible if entry belongs to currently authenticated
     *         user. An exception will be thrown otherwise.
     * @throws Zend_Gdata_App_HttpException
     * @return Zend_Gdata_YouTube_VideoEntry The video entry found at the
     *         specified URL.
     */
    public function getVideoEntry($videoId = null, $location = null,
        $fullEntry = false)
    {
        if ($videoId !== null) {
            if ($fullEntry) {
                return $this->getFullVideoEntry($videoId);
            } else {
                $uri = self::VIDEO_URI . "/" . $videoId;
            }
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_YouTube_VideoEntry');
    }

    /**
     * Retrieves a video entry from the user's upload feed.
     *
     * @param mixed $videoID The ID of the video to retrieve.
     * @throws Zend_Gdata_App_HttpException
     * @return Zend_Gdata_YouTube_VideoEntry|null The video entry to be
     *          retrieved, or null if it was not found or the user requesting it
     *          did not have the appropriate permissions.
     */
    public function getFullVideoEntry($videoId)
    {
        $uri = self::USER_URI . "/default/" .
            self::UPLOADS_URI_SUFFIX . "/$videoId";
        return parent::getEntry($uri, 'Zend_Gdata_YouTube_VideoEntry');
    }

    /**
     * Retrieves a feed of videos related to the specified video ID.
     *
     * @param string $videoId The videoId of interest
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined
     * @return Zend_Gdata_YouTube_VideoFeed The feed of videos found at the
     *         specified URL.
     */
    public function getRelatedVideoFeed($videoId = null, $location = null)
    {
        if ($videoId !== null) {
            $uri = self::VIDEO_URI . "/" . $videoId . "/" .
                self::RELATED_URI_SUFFIX;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    /**
     * Retrieves a feed of video responses related to the specified video ID.
     *
     * @param string $videoId The videoId of interest
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined
     * @return Zend_Gdata_YouTube_VideoFeed The feed of videos found at the
     *         specified URL.
     */
    public function getVideoResponseFeed($videoId = null, $location = null)
    {
        if ($videoId !== null) {
            $uri = self::VIDEO_URI . "/" . $videoId . "/" .
                self::RESPONSES_URI_SUFFIX;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    /**
     * Retrieves a feed of comments related to the specified video ID.
     *
     * @param string $videoId The videoId of interest
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined
     * @return Zend_Gdata_YouTube_CommentFeed The feed of videos found at the
     *         specified URL.
     */
    public function getVideoCommentFeed($videoId = null, $location = null)
    {
        if ($videoId !== null) {
            $uri = self::VIDEO_URI . "/" . $videoId . "/comments";
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_CommentFeed');
    }

    /**
     * Retrieves a feed of comments related to the specified video ID.
     *
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined
     * @return Zend_Gdata_YouTube_CommentFeed The feed of videos found at the
     *         specified URL.
     */
    public function getTopRatedVideoFeed($location = null)
    {
        $standardFeedUri = self::STANDARD_TOP_RATED_URI;

        if ($this->getMajorProtocolVersion() == 2) {
            $standardFeedUri = self::STANDARD_TOP_RATED_URI_V2;
        }

        if ($location == null) {
            $uri = $standardFeedUri;
        } else if ($location instanceof Zend_Gdata_Query) {
            if ($location instanceof Zend_Gdata_YouTube_VideoQuery) {
                if (!isset($location->url)) {
                    $location->setFeedType('top rated');
                }
            }
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }


    /**
     * Retrieves a feed of the most viewed videos.
     *
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined
     * @return Zend_Gdata_YouTube_VideoFeed The feed of videos found at the
     *         specified URL.
     */
    public function getMostViewedVideoFeed($location = null)
    {
        $standardFeedUri = self::STANDARD_MOST_VIEWED_URI;

        if ($this->getMajorProtocolVersion() == 2) {
            $standardFeedUri = self::STANDARD_MOST_VIEWED_URI_V2;
        }

        if ($location == null) {
            $uri = $standardFeedUri;
        } else if ($location instanceof Zend_Gdata_Query) {
            if ($location instanceof Zend_Gdata_YouTube_VideoQuery) {
                if (!isset($location->url)) {
                    $location->setFeedType('most viewed');
                }
            }
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    /**
     * Retrieves a feed of recently featured videos.
     *
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined
     * @return Zend_Gdata_YouTube_VideoFeed The feed of videos found at the
     *         specified URL.
     */
    public function getRecentlyFeaturedVideoFeed($location = null)
    {
        $standardFeedUri = self::STANDARD_RECENTLY_FEATURED_URI;

        if ($this->getMajorProtocolVersion() == 2) {
            $standardFeedUri = self::STANDARD_RECENTLY_FEATURED_URI_V2;
        }

        if ($location == null) {
            $uri = $standardFeedUri;
        } else if ($location instanceof Zend_Gdata_Query) {
            if ($location instanceof Zend_Gdata_YouTube_VideoQuery) {
                if (!isset($location->url)) {
                    $location->setFeedType('recently featured');
                }
            }
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    /**
     * Retrieves a feed of videos recently featured for mobile devices.
     * These videos will have RTSP links in the $entry->mediaGroup->content
     *
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined
     * @return Zend_Gdata_YouTube_VideoFeed The feed of videos found at the
     *         specified URL.
     */
    public function getWatchOnMobileVideoFeed($location = null)
    {
        $standardFeedUri = self::STANDARD_WATCH_ON_MOBILE_URI;

        if ($this->getMajorProtocolVersion() == 2) {
            $standardFeedUri = self::STANDARD_WATCH_ON_MOBILE_URI_V2;
        }

        if ($location == null) {
            $uri = $standardFeedUri;
        } else if ($location instanceof Zend_Gdata_Query) {
            if ($location instanceof Zend_Gdata_YouTube_VideoQuery) {
                if (!isset($location->url)) {
                    $location->setFeedType('watch on mobile');
                }
            }
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    /**
     * Retrieves a feed which lists a user's playlist
     *
     * @param string $user (optional) The username of interest
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined
     * @return Zend_Gdata_YouTube_PlaylistListFeed The feed of playlists
     */
    public function getPlaylistListFeed($user = null, $location = null)
    {
        if ($user !== null) {
            $uri = self::USER_URI . '/' . $user . '/playlists';
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_PlaylistListFeed');
    }

    /**
     * Retrieves a feed of videos in a particular playlist
     *
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined
     * @return Zend_Gdata_YouTube_PlaylistVideoFeed The feed of videos found at
     *         the specified URL.
     */
    public function getPlaylistVideoFeed($location)
    {
        if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_PlaylistVideoFeed');
    }

    /**
     * Retrieves a feed of a user's subscriptions
     *
     * @param string $user (optional) The username of interest
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined
     * @return Zend_Gdata_YouTube_SubscriptionListFeed The feed of subscriptions
     */
    public function getSubscriptionFeed($user = null, $location = null)
    {
        if ($user !== null) {
            $uri = self::USER_URI . '/' . $user . '/subscriptions';
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_SubscriptionFeed');
    }

    /**
     * Retrieves a feed of a user's contacts
     *
     * @param string $user (optional) The username of interest
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined
     * @return Zend_Gdata_YouTube_ContactFeed The feed of contacts
     */
    public function getContactFeed($user = null, $location = null)
    {
        if ($user !== null) {
            $uri = self::USER_URI . '/' . $user . '/contacts';
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_ContactFeed');
    }

    /**
     * Retrieves a user's uploads
     *
     * @param string $user (optional) The username of interest
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined
     * @return Zend_Gdata_YouTube_VideoFeed The videos uploaded by the user
     */
    public function getUserUploads($user = null, $location = null)
    {
        if ($user !== null) {
            $uri = self::USER_URI . '/' . $user . '/' .
                   self::UPLOADS_URI_SUFFIX;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    /**
     * Retrieves a user's favorites
     *
     * @param string $user (optional) The username of interest
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined
     * @return Zend_Gdata_YouTube_VideoFeed The videos favorited by the user
     */
    public function getUserFavorites($user = null, $location = null)
    {
        if ($user !== null) {
            $uri = self::USER_URI . '/' . $user . '/' .
                   self::FAVORITES_URI_SUFFIX;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_YouTube_VideoFeed');
    }

    /**
     * Retrieves a user's profile as an entry
     *
     * @param string $user (optional) The username of interest
     * @param mixed $location (optional) The URL to query or a
     *         Zend_Gdata_Query object from which a URL can be determined
     * @return Zend_Gdata_YouTube_UserProfileEntry The user profile entry
     */
    public function getUserProfile($user = null, $location = null)
    {
        if ($user !== null) {
            $uri = self::USER_URI . '/' . $user;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_YouTube_UserProfileEntry');
    }

    /**
     * Helper function for parsing a YouTube token response
     *
     * @param string $response The service response
     * @throws Zend_Gdata_App_Exception
     * @return array An array containing the token and URL
     */
    public static function parseFormUploadTokenResponse($response)
    {
        // Load the feed as an XML DOMDocument object
        @ini_set('track_errors', 1);
        $doc = new DOMDocument();
        $doc = @Zend_Xml_Security::scan($response, $doc);
        @ini_restore('track_errors');

        if (!$doc) {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception(
                "Zend_Gdata_YouTube::parseFormUploadTokenResponse - " .
                "DOMDocument cannot parse XML: $php_errormsg");
        }
        $responseElement = $doc->getElementsByTagName('response')->item(0);

        $urlText = null;
        $tokenText = null;
        if ($responseElement != null) {
            $urlElement =
                $responseElement->getElementsByTagName('url')->item(0);
            $tokenElement =
                $responseElement->getElementsByTagName('token')->item(0);

            if ($urlElement && $urlElement->hasChildNodes() &&
                $tokenElement && $tokenElement->hasChildNodes()) {

                $urlText = $urlElement->firstChild->nodeValue;
                $tokenText = $tokenElement->firstChild->nodeValue;
            }
        }

        if ($tokenText != null && $urlText != null) {
            return array('token' => $tokenText, 'url' => $urlText);
        } else {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception(
                'Form upload token not found in response');
        }
    }

    /**
     * Retrieves a YouTube token
     *
     * @param Zend_Gdata_YouTube_VideoEntry $videoEntry The video entry
     * @param string $url The location as a string URL
     * @throws Zend_Gdata_App_Exception
     * @return array An array containing a token and URL
     */
    public function getFormUploadToken($videoEntry,
        $url='https://gdata.youtube.com/action/GetUploadToken')
    {
        if ($url != null && is_string($url)) {
            // $response is a Zend_Http_response object
            $response = $this->post($videoEntry, $url);
            return self::parseFormUploadTokenResponse($response->getBody());
        } else {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception(
                'Url must be provided as a string URL');
        }
    }

    /**
     * Retrieves the activity feed for users
     *
     * @param mixed $usernames A string identifying the usernames for which to
     *              retrieve activity for. This can also be a Zend_Gdata_Query
     *              object from which a URL can be determined.
     * @throws Zend_Gdata_App_VersionException if using version less than 2.
     * @return Zend_Gdata_YouTube_ActivityFeed
     */
    public function getActivityForUser($username)
    {
        if ($this->getMajorProtocolVersion() == 1) {
            #require_once 'Zend/Gdata/App/VersionException.php';
            throw new Zend_Gdata_App_VersionException('User activity feeds ' .
                'are not available in API version 1.');
        }

        $uri = null;
        if ($username instanceof Zend_Gdata_Query) {
            $uri = $username->getQueryUrl($this->getMajorProtocolVersion());
        } else {
            if (count(explode(',', $username)) >
                self::ACTIVITY_FEED_MAX_USERS) {
                #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException(
                    'Activity feed can only retrieve for activity for up to ' .
                    self::ACTIVITY_FEED_MAX_USERS .  ' users per request');
            }
            $uri = self::ACTIVITY_FEED_URI . '?author=' . $username;
        }

        return parent::getFeed($uri, 'Zend_Gdata_YouTube_ActivityFeed');
    }

    /**
     * Retrieve the activity of the currently authenticated users friend.
     *
     * @throws Zend_Gdata_App_Exception if not logged in.
     * @return Zend_Gdata_YouTube_ActivityFeed
     */
    public function getFriendActivityForCurrentUser()
    {
        if (!$this->isAuthenticated()) {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception('You must be authenticated to ' .
                'use the getFriendActivityForCurrentUser function in Zend_' .
                'Gdata_YouTube.');
        }
        return parent::getFeed(self::FRIEND_ACTIVITY_FEED_URI,
            'Zend_Gdata_YouTube_ActivityFeed');
    }

    /**
     * Retrieve a feed of messages in the currently authenticated user's inbox.
     *
     * @throws Zend_Gdata_App_Exception if not logged in.
     * @return Zend_Gdata_YouTube_InboxFeed|null
     */
    public function getInboxFeedForCurrentUser()
    {
        if (!$this->isAuthenticated()) {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception('You must be authenticated to ' .
                'use the getInboxFeedForCurrentUser function in Zend_' .
                'Gdata_YouTube.');
        }

        return parent::getFeed(self::INBOX_FEED_URI,
            'Zend_Gdata_YouTube_InboxFeed');
    }

    /**
     * Send a video message.
     *
     * Note: Either a Zend_Gdata_YouTube_VideoEntry or a valid video ID must
     * be provided.
     *
     * @param string $body The body of the message
     * @param Zend_Gdata_YouTube_VideoEntry (optional) The video entry to send
     * @param string $videoId The id of the video to send
     * @param string $recipientUserName The username of the recipient
     * @throws Zend_Gdata_App_InvalidArgumentException if no valid
     *         Zend_Gdata_YouTube_VideoEntry or videoId were provided
     * @return Zend_Gdata_YouTube_InboxEntry|null The
     *         Zend_Gdata_YouTube_Inbox_Entry representing the sent message.
     *
     */
    public function sendVideoMessage($body, $videoEntry = null,
        $videoId = null, $recipientUserName)
    {
        if (!$videoId && !$videoEntry) {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                'Expecting either a valid videoID or a videoEntry object in ' .
                'Zend_Gdata_YouTube->sendVideoMessage().');
        }

        $messageEntry = new Zend_Gdata_YouTube_InboxEntry();

        if ($this->getMajorProtocolVersion() == null ||
            $this->getMajorProtocolVersion() == 1) {

            if (!$videoId) {
                $videoId = $videoEntry->getVideoId();
            } elseif (strlen($videoId) < 12) {
                //Append the full URI
                $videoId = self::VIDEO_URI . '/' . $videoId;
            }

            $messageEntry->setId($this->newId($videoId));
            // TODO there seems to be a bug where v1 inbox entries dont
            // retain their description...
            $messageEntry->setDescription(
                new Zend_Gdata_YouTube_Extension_Description($body));

        } else {
            if (!$videoId) {
                $videoId = $videoEntry->getVideoId();
                $videoId = substr($videoId, strrpos($videoId, ':'));
            }
            $messageEntry->setId($this->newId($videoId));
            $messageEntry->setSummary($this->newSummary($body));
        }

        $insertUrl = 'https://gdata.youtube.com/feeds/api/users/' .
            $recipientUserName . '/inbox';
        $response = $this->insertEntry($messageEntry, $insertUrl,
            'Zend_Gdata_YouTube_InboxEntry');
        return $response;
    }

    /**
     * Post a comment in reply to an existing comment
     *
     * @param Zend_Gdata_YouTube_CommentEntry $commentEntry The comment entry
     *         to reply to
     * @param string                          $commentText  The text of the
		 *         comment to post
     * @return Zend_Gdata_YouTube_CommentEntry the posted comment
     */
    public function replyToCommentEntry($commentEntry, $commentText)
    {
        $newComment = $this->newCommentEntry();
        $newComment->content = $this->newContent()->setText($commentText);
        $commentId = $commentEntry->getId();
        $commentIdArray = explode(':', $commentId);

        // create a new link element
        $inReplyToLinkHref = self::VIDEO_URI . '/' . $commentIdArray[3] .
            '/comments/' . $commentIdArray[5];
        $inReplyToLink = $this->newLink($inReplyToLinkHref,
            self::IN_REPLY_TO_SCHEME, $type="application/atom+xml");
        $links = $newComment->getLink();
        $links[] = $inReplyToLink;
        $newComment->setLink($links);
        $commentFeedPostUrl = self::VIDEO_URI . '/' . $commentIdArray[3] .
            '/comments';
        return $this->insertEntry($newComment,
            $commentFeedPostUrl, 'Zend_Gdata_YouTube_CommentEntry');
    }

}
