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
 * @subpackage Calendar
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata
 */
#require_once 'Zend/Gdata.php';

/**
 * @see Zend_Gdata_Calendar_EventFeed
 */
#require_once 'Zend/Gdata/Calendar/EventFeed.php';

/**
 * @see Zend_Gdata_Calendar_EventEntry
 */
#require_once 'Zend/Gdata/Calendar/EventEntry.php';

/**
 * @see Zend_Gdata_Calendar_ListFeed
 */
#require_once 'Zend/Gdata/Calendar/ListFeed.php';

/**
 * @see Zend_Gdata_Calendar_ListEntry
 */
#require_once 'Zend/Gdata/Calendar/ListEntry.php';

/**
 * Service class for interacting with the Google Calendar data API
 * @link http://code.google.com/apis/gdata/calendar.html
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Calendar
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Calendar extends Zend_Gdata
{

    const CALENDAR_FEED_URI = 'https://www.google.com/calendar/feeds';
    const CALENDAR_EVENT_FEED_URI = 'https://www.google.com/calendar/feeds/default/private/full';
    const AUTH_SERVICE_NAME = 'cl';

    protected $_defaultPostUri = self::CALENDAR_EVENT_FEED_URI;

    /**
     * Namespaces used for Zend_Gdata_Calendar
     *
     * @var array
     */
    public static $namespaces = array(
        array('gCal', 'http://schemas.google.com/gCal/2005', 1, 0)
    );

    /**
     * Create Gdata_Calendar object
     *
     * @param Zend_Http_Client $client (optional) The HTTP client to use when
     *          when communicating with the Google servers.
     * @param string $applicationId The identity of the app in the form of Company-AppName-Version
     */
    public function __construct($client = null, $applicationId = 'MyCompany-MyApp-1.0')
    {
        $this->registerPackage('Zend_Gdata_Calendar');
        $this->registerPackage('Zend_Gdata_Calendar_Extension');
        parent::__construct($client, $applicationId);
        $this->_httpClient->setParameterPost('service', self::AUTH_SERVICE_NAME);
    }

    /**
     * Retreive feed object
     *
     * @param mixed $location The location for the feed, as a URL or Query
     * @return Zend_Gdata_Calendar_EventFeed
     */
    public function getCalendarEventFeed($location = null)
    {
        if ($location == null) {
            $uri = self::CALENDAR_EVENT_FEED_URI;
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getFeed($uri, 'Zend_Gdata_Calendar_EventFeed');
    }

    /**
     * Retreive entry object
     *
     * @return Zend_Gdata_Calendar_EventEntry
     */
    public function getCalendarEventEntry($location = null)
    {
        if ($location == null) {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Location must not be null');
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri, 'Zend_Gdata_Calendar_EventEntry');
    }


    /**
     * Retrieve feed object
     *
     * @return Zend_Gdata_Calendar_ListFeed
     */
    public function getCalendarListFeed()
    {
        $uri = self::CALENDAR_FEED_URI . '/default';
        return parent::getFeed($uri,'Zend_Gdata_Calendar_ListFeed');
    }

    /**
     * Retreive entryobject
     *
     * @return Zend_Gdata_Calendar_ListEntry
     */
    public function getCalendarListEntry($location = null)
    {
        if ($location == null) {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Location must not be null');
        } else if ($location instanceof Zend_Gdata_Query) {
            $uri = $location->getQueryUrl();
        } else {
            $uri = $location;
        }
        return parent::getEntry($uri,'Zend_Gdata_Calendar_ListEntry');
    }

    public function insertEvent($event, $uri=null)
    {
        if ($uri == null) {
            $uri = $this->_defaultPostUri;
        }
        $newEvent = $this->insertEntry($event, $uri, 'Zend_Gdata_Calendar_EventEntry');
        return $newEvent;
    }

}
