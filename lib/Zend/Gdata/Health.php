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
 * @subpackage Health
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Health.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata
 */
#require_once 'Zend/Gdata.php';

/**
 * @see Zend_Gdata_Health_ProfileFeed
 */
#require_once 'Zend/Gdata/Health/ProfileFeed.php';

/**
 * @see Zend_Gdata_Health_ProfileListFeed
 */
#require_once 'Zend/Gdata/Health/ProfileListFeed.php';

/**
 * @see Zend_Gdata_Health_ProfileListEntry
 */
#require_once 'Zend/Gdata/Health/ProfileListEntry.php';

/**
 * @see Zend_Gdata_Health_ProfileEntry
 */
#require_once 'Zend/Gdata/Health/ProfileEntry.php';

/**
 * Service class for interacting with the Google Health Data API
 *
 * @link http://code.google.com/apis/health
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Health
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Health extends Zend_Gdata
{
    /**
     * URIs of the AuthSub/OAuth feeds.
     */
    const AUTHSUB_PROFILE_FEED_URI =
        'https://www.google.com/health/feeds/profile/default';
    const AUTHSUB_REGISTER_FEED_URI =
        'https://www.google.com/health/feeds/register/default';

    /**
     * URIs of the ClientLogin feeds.
     */
    const CLIENTLOGIN_PROFILELIST_FEED_URI =
        'https://www.google.com/health/feeds/profile/list';
    const CLIENTLOGIN_PROFILE_FEED_URI =
        'https://www.google.com/health/feeds/profile/ui';
    const CLIENTLOGIN_REGISTER_FEED_URI =
        'https://www.google.com/health/feeds/register/ui';

    /**
     * Authentication service names for Google Health and the H9 Sandbox.
     */
    const HEALTH_SERVICE_NAME = 'health';
    const H9_SANDBOX_SERVICE_NAME = 'weaver';

    /**
     * Profile ID used for all API interactions.  This can only be set when
     * using ClientLogin for authentication.
     *
     * @var string
     */
    private $_profileID = null;

    /**
     * True if API calls should be made to the H9 developer sandbox at /h9
     * rather than /health
     *
     * @var bool
     */
    private $_useH9Sandbox = false;

    public static $namespaces =
        array('ccr' => 'urn:astm-org:CCR',
              'batch' => 'http://schemas.google.com/gdata/batch',
              'h9m' => 'http://schemas.google.com/health/metadata',
              'gAcl' => 'http://schemas.google.com/acl/2007',
              'gd' => 'http://schemas.google.com/g/2005');

    /**
     * Create Zend_Gdata_Health object
     *
     * @param Zend_Http_Client $client (optional) The HTTP client to use when
     *     when communicating with the Google Health servers.
     * @param string $applicationId The identity of the application in the form
     *     of Company-AppName-Version
     * @param bool $useH9Sandbox True if the H9 Developer's Sandbox should be
     *     used instead of production Google Health.
     */
    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0', $useH9Sandbox = false)
    {
        $this->registerPackage('Zend_Gdata_Health');
        $this->registerPackage('Zend_Gdata_Health_Extension_Ccr');
        parent::__construct($client, $applicationId);
        $this->_useH9Sandbox = $useH9Sandbox;
    }

    /**
     * Gets the id of the user's profile
     *
     * @return string The profile id
     */
    public function getProfileID()
    {
        return $this->_profileID;
    }

    /**
     * Sets which of the user's profiles will be used
     *
     * @param string $id The profile ID
     * @return Zend_Gdata_Health Provides a fluent interface
     */
    public function setProfileID($id) {
        $this->_profileID = $id;
        return $this;
    }

     /**
     * Retrieves the list of profiles associated with the user's ClientLogin
     * credentials.
     *
     * @param string $query The query of the feed as a URL or Query object
     * @return Zend_Gdata_Feed
     */
    public function getHealthProfileListFeed($query = null)
    {
        if ($this->_httpClient->getClientLoginToken() === null) {
            #require_once 'Zend/Gdata/App/AuthException.php';
            throw new Zend_Gdata_App_AuthException(
                'Profiles list feed is only available when using ClientLogin');
        }

        if($query === null)  {
            $uri = self::CLIENTLOGIN_PROFILELIST_FEED_URI;
        } else if ($query instanceof Zend_Gdata_Query) {
            $uri = $query->getQueryUrl();
        } else {
            $uri = $query;
        }

        // use correct feed for /h9 or /health
        if ($this->_useH9Sandbox) {
            $uri = preg_replace('/\/health\//', '/h9/', $uri);
        }

        return parent::getFeed($uri, 'Zend_Gdata_Health_ProfileListFeed');
    }

    /**
     * Retrieve a user's profile as a feed object.  If ClientLogin is used, the
     * profile associated with $this->_profileID is returned, otherwise
     * the profile associated with the AuthSub token is read.
     *
     * @param mixed $query The query for the feed, as a URL or Query
     * @return Zend_Gdata_Health_ProfileFeed
     */
    public function getHealthProfileFeed($query = null)
    {
        if ($this->_httpClient->getClientLoginToken() !== null &&
            $this->getProfileID() == null) {
            #require_once 'Zend/Gdata/App/AuthException.php';
            throw new Zend_Gdata_App_AuthException(
                'Profile ID must not be null. Did you call setProfileID()?');
        }

        if ($query instanceof Zend_Gdata_Query) {
            $uri = $query->getQueryUrl();
        } else if ($this->_httpClient->getClientLoginToken() !== null &&
                   $query == null) {
            $uri = self::CLIENTLOGIN_PROFILE_FEED_URI . '/' . $this->getProfileID();
        } else if ($query === null) {
            $uri = self::AUTHSUB_PROFILE_FEED_URI;
        } else {
            $uri = $query;
        }

        // use correct feed for /h9 or /health
        if ($this->_useH9Sandbox) {
            $uri = preg_replace('/\/health\//', '/h9/', $uri);
        }

        return parent::getFeed($uri, 'Zend_Gdata_Health_ProfileFeed');
    }

    /**
     * Retrieve a profile entry object
     *
     * @param mixed $query The query for the feed, as a URL or Query
     * @return Zend_Gdata_Health_ProfileEntry
     */
    public function getHealthProfileEntry($query = null)
    {
        if ($query === null) {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                'Query must not be null');
        } else if ($query instanceof Zend_Gdata_Query) {
            $uri = $query->getQueryUrl();
        } else {
            $uri = $query;
        }
        return parent::getEntry($uri, 'Zend_Gdata_Health_ProfileEntry');
    }

    /**
     * Posts a new notice using the register feed.  This function constructs
     * the atom profile entry.
     *
     * @param string $subject The subject line of the notice
     * @param string $body The message body of the notice
     * @param string $bodyType The (optional) type of message body
     *     (text, xhtml, html, etc.)
     * @param string $ccrXML The (optional) CCR to add to the user's profile
     * @return Zend_Gdata_Health_ProfileEntry
     */
    public function sendHealthNotice($subject, $body, $bodyType = null, $ccrXML = null)
    {
        if ($this->_httpClient->getClientLoginToken()) {
            $profileID = $this->getProfileID();
            if ($profileID !== null) {
                $uri = self::CLIENTLOGIN_REGISTER_FEED_URI . '/' . $profileID;
            } else {
                #require_once 'Zend/Gdata/App/AuthException.php';
                throw new Zend_Gdata_App_AuthException(
                    'Profile ID must not be null. Did you call setProfileID()?');
            }
        } else {
            $uri = self::AUTHSUB_REGISTER_FEED_URI;
        }

        $entry = new Zend_Gdata_Health_ProfileEntry();
        $entry->title = $this->newTitle($subject);
        $entry->content = $this->newContent($body);
        $entry->content->type = $bodyType ? $bodyType : 'text';
        $entry->setCcr($ccrXML);

        // use correct feed for /h9 or /health
        if ($this->_useH9Sandbox) {
            $uri = preg_replace('/\/health\//', '/h9/', $uri);
        }

        return $this->insertEntry($entry, $uri, 'Zend_Gdata_Health_ProfileEntry');
    }
}
