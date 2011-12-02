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
 * @subpackage Books
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Books.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata
 */
#require_once 'Zend/Gdata.php';

/**
 * @see Zend_Gdata_DublinCore
 */
#require_once 'Zend/Gdata/DublinCore.php';

/**
 * @see Zend_Gdata_Books_CollectionEntry
 */
#require_once 'Zend/Gdata/Books/CollectionEntry.php';

/**
 * @see Zend_Gdata_Books_CollectionFeed
 */
#require_once 'Zend/Gdata/Books/CollectionFeed.php';

/**
 * @see Zend_Gdata_Books_VolumeEntry
 */
#require_once 'Zend/Gdata/Books/VolumeEntry.php';

/**
 * @see Zend_Gdata_Books_VolumeFeed
 */
#require_once 'Zend/Gdata/Books/VolumeFeed.php';

/**
 * Service class for interacting with the Books service
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Books
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Books extends Zend_Gdata
{
    const VOLUME_FEED_URI = 'http://books.google.com/books/feeds/volumes';
    const MY_LIBRARY_FEED_URI = 'http://books.google.com/books/feeds/users/me/collections/library/volumes';
    const MY_ANNOTATION_FEED_URI = 'http://books.google.com/books/feeds/users/me/volumes';
    const AUTH_SERVICE_NAME = 'print';

    /**
     * Namespaces used for Zend_Gdata_Books
     *
     * @var array
     */
    public static $namespaces = array(
        array('gbs', 'http://schemas.google.com/books/2008', 1, 0),
        array('dc', 'http://purl.org/dc/terms', 1, 0)
    );

    /**
     * Create Zend_Gdata_Books object
     *
     * @param Zend_Http_Client $client (optional) The HTTP client to use when
     *          when communicating with the Google servers.
     * @param string $applicationId The identity of the app in the form of Company-AppName-Version
     */
    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_Books');
        $this->registerPackage('Zend_Gdata_Books_Extension');
        parent::__construct($client, $applicationId);
        $this->_httpClient->setParameterPost('service', self::AUTH_SERVICE_NAME);
     }

    /**
     * Retrieves a feed of volumes.
     *
     * @param Zend_Gdata_Query|string|null $location (optional) The URL to
     *        query or a Zend_Gdata_Query object from which a URL can be
     *        determined.
     * @return Zend_Gdata_Books_VolumeFeed The feed of volumes found at the
     *         specified URL.
     */
    public function getVolumeFeed($location = null)
    {
        if ($location == null) {
            $uri = self::VOLUME_FEED_URI;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Books_VolumeFeed');
    }

    /**
     * Retrieves a specific volume entry.
     *
     * @param string|null $volumeId The volumeId of interest.
     * @param Zend_Gdata_Query|string|null $location (optional) The URL to
     *        query or a Zend_Gdata_Query object from which a URL can be
     *        determined.
     * @return Zend_Gdata_Books_VolumeEntry The feed of volumes found at the
     *         specified URL.
     */
    public function getVolumeEntry($volumeId = null, $location = null)
    {
        if ($volumeId !== null) {
            $uri = self::VOLUME_FEED_URI . "/" . $volumeId;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_Books_VolumeEntry');
    }

    /**
     * Retrieves a feed of volumes, by default the User library feed.
     *
     * @param Zend_Gdata_Query|string|null $location (optional) The URL to
     *        query.
     * @return Zend_Gdata_Books_VolumeFeed The feed of volumes found at the
     *         specified URL.
     */
    public function getUserLibraryFeed($location = null)
    {
        if ($location == null) {
            $uri = self::MY_LIBRARY_FEED_URI;
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Books_VolumeFeed');
    }

    /**
     * Retrieves a feed of volumes, by default the User annotation feed
     *
     * @param Zend_Gdata_Query|string|null $location (optional) The URL to
     *        query.
     * @return Zend_Gdata_Books_VolumeFeed The feed of volumes found at the
     *         specified URL.
     */
    public function getUserAnnotationFeed($location = null)
    {
        if ($location == null) {
            $uri = self::MY_ANNOTATION_FEED_URI;
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Books_VolumeFeed');
    }

    /**
     * Insert a Volume / Annotation
     *
     * @param Zend_Gdata_Books_VolumeEntry $entry
     * @param Zend_Gdata_Query|string|null $location (optional) The URL to
     *        query
     * @return Zend_Gdata_Books_VolumeEntry The inserted volume entry.
     */
    public function insertVolume($entry, $location = null)
    {
        if ($location == null) {
            $uri = self::MY_LIBRARY_FEED_URI;
        } else {
            $uri = $location;
        }
        return parent::insertEntry(
            $entry, $uri, 'Zend_Gdata_Books_VolumeEntry');
    }

    /**
     * Delete a Volume
     *
     * @param Zend_Gdata_Books_VolumeEntry $entry
     * @return void
     */
    public function deleteVolume($entry)
    {
        $entry->delete();
    }

}
