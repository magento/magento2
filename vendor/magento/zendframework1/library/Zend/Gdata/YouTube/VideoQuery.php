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
 * Zend_Gdata_YouTube
 */
#require_once('Zend/Gdata/YouTube.php');

/**
 * Zend_Gdata_Query
 */
#require_once('Zend/Gdata/Query.php');

/**
 * Assists in constructing queries for YouTube videos
 *
 * @link http://code.google.com/apis/youtube/
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage YouTube
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_YouTube_VideoQuery extends Zend_Gdata_Query
{

    /**
     * Create Gdata_YouTube_VideoQuery object
     */
    public function __construct($url = null)
    {
        parent::__construct($url);
    }

    /**
     * Sets the type of feed this query should be used to search
     *
     * @param string $feedType The type of feed
     * @param string $videoId The ID of the video associated with this query
     * @param string $entry The ID of the entry associated with this query
     */
    public function setFeedType($feedType, $videoId = null, $entry = null)
    {
        switch ($feedType) {
        case 'top rated':
            $this->_url = Zend_Gdata_YouTube::STANDARD_TOP_RATED_URI;
            break;
        case 'most viewed':
            $this->_url = Zend_Gdata_YouTube::STANDARD_MOST_VIEWED_URI;
            break;
        case 'recently featured':
            $this->_url = Zend_Gdata_YouTube::STANDARD_RECENTLY_FEATURED_URI;
            break;
        case 'mobile':
            $this->_url = Zend_Gdata_YouTube::STANDARD_WATCH_ON_MOBILE_URI;
            break;
        case 'related':
            if ($videoId === null) {
                #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException(
                    'Video ID must be set for feed of type: ' . $feedType);
            } else {
                $this->_url = Zend_Gdata_YouTube::VIDEO_URI . '/' . $videoId .
                    '/related';
            }
            break;
        case 'responses':
            if ($videoId === null) {
                #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_Exception(
                    'Video ID must be set for feed of type: ' . $feedType);
            } else {
                $this->_url = Zend_Gdata_YouTube::VIDEO_URI . '/' . $videoId .
                    '/responses';
            }
            break;
        case 'comments':
            if ($videoId === null) {
                #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_Exception(
                    'Video ID must be set for feed of type: ' . $feedType);
            } else {
                $this->_url = Zend_Gdata_YouTube::VIDEO_URI . '/' .
                    $videoId . '/comments';
                if ($entry !== null) {
                    $this->_url .= '/' . $entry;
                }
            }
            break;
        default:
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception('Unknown feed type');
            break;
        }
    }

    /**
     * Sets the location parameter for the query
     *
     * @param string $value
     * @throws Zend_Gdata_App_InvalidArgumentException
     * @return Zend_Gdata_YouTube_VideoQuery Provides a fluent interface
     */
    public function setLocation($value)
    {
        switch($value) {
            case null:
                unset($this->_params['location']);
            default:
                $parameters = explode(',', $value);
                if (count($parameters) != 2) {
                    #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                    throw new Zend_Gdata_App_InvalidArgumentException(
                        'You must provide 2 coordinates to the location ' .
                        'URL parameter');
                }

                foreach($parameters as $param) {
                    $temp = trim($param);
                    // strip off the optional exclamation mark for numeric check
                    if (substr($temp, -1) == '!') {
                        $temp = substr($temp, 0, -1);
                    }
                    if (!is_numeric($temp)) {
                        #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                        throw new Zend_Gdata_App_InvalidArgumentException(
                            'Value provided to location parameter must' .
                            ' be in the form of two coordinates');
                    }
                }
                $this->_params['location'] = $value;
        }
    }

    /**
     * Get the value of the location parameter
     *
     * @return string|null Return the location if it exists, null otherwise.
     */
    public function getLocation()
    {
        if (array_key_exists('location', $this->_params)) {
            return $this->_params['location'];
        } else {
            return null;
        }
    }


    /**
     * Sets the location-radius parameter for the query
     *
     * @param string $value
     * @return Zend_Gdata_YouTube_VideoQuery Provides a fluent interface
     */
    public function setLocationRadius($value)
    {
        switch($value) {
            case null:
                unset($this->_params['location-radius']);
            default:
                $this->_params['location-radius'] = $value;
        }
    }

    /**
     * Get the value of the location-radius parameter
     *
     * @return string|null Return the location-radius if it exists,
     * null otherwise.
     */
    public function getLocationRadius()
    {
        if (array_key_exists('location-radius', $this->_params)) {
            return $this->_params['location-radius'];
        } else {
            return null;
        }
    }

    /**
     * Sets the time period over which this query should apply
     *
     * @param string $value
     * @throws Zend_Gdata_App_InvalidArgumentException
     * @return Zend_Gdata_YouTube_VideoQuery Provides a fluent interface
     */
    public function setTime($value = null)
    {
        switch ($value) {
            case 'today':
                $this->_params['time'] = 'today';
                break;
            case 'this_week':
                $this->_params['time'] = 'this_week';
                break;
            case 'this_month':
                $this->_params['time'] = 'this_month';
                break;
            case 'all_time':
                $this->_params['time'] = 'all_time';
                break;
            case null:
                unset($this->_params['time']);
            default:
                #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException(
                    'Unknown time value');
                break;
        }
        return $this;
    }

    /**
     * Sets the value of the uploader parameter
     *
     * @param string $value The value of the uploader parameter. Currently this
     *        can only be set to the value of 'partner'.
     * @throws Zend_Gdata_App_InvalidArgumentException
     * @return Zend_Gdata_YouTube_VideoQuery Provides a fluent interface
     */
    public function setUploader($value = null)
    {
        switch ($value) {
            case 'partner':
                $this->_params['uploader'] = 'partner';
                break;
            case null:
                unset($this->_params['uploader']);
                break;
            default:
                #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException(
                    'Unknown value for uploader');
        }
        return $this;
    }

    /**
     * Sets the formatted video query (vq) URL param value
     *
     * @param string $value
     * @return Zend_Gdata_YouTube_VideoQuery Provides a fluent interface
     */
    public function setVideoQuery($value = null)
    {
        if ($value != null) {
            $this->_params['vq'] = $value;
        } else {
            unset($this->_params['vq']);
        }
        return $this;
    }

    /**
     * Sets the param to return videos of a specific format
     *
     * @param string $value
     * @return Zend_Gdata_YouTube_VideoQuery Provides a fluent interface
     */
    public function setFormat($value = null)
    {
        if ($value != null) {
            $this->_params['format'] = $value;
        } else {
            unset($this->_params['format']);
        }
        return $this;
    }

    /**
     * Sets whether or not to include racy videos in the search results
     *
     * @param string $value
     * @return Zend_Gdata_YouTube_VideoQuery Provides a fluent interface
     */
    public function setRacy($value = null)
    {
        switch ($value) {
            case 'include':
                $this->_params['racy'] = $value;
                break;
            case 'exclude':
                $this->_params['racy'] = $value;
                break;
            case null:
                unset($this->_params['racy']);
                break;
        }
        return $this;
    }

    /**
     * Whether or not to include racy videos in the search results
     *
     * @return string|null The value of racy if it exists, null otherwise.
     */
    public function getRacy()
    {
        if (array_key_exists('racy', $this->_params)) {
            return $this->_params['racy'];
        } else {
            return null;
        }
    }

    /**
     * Set the safeSearch parameter
     *
     * @param string $value The value of the parameter, currently only 'none',
     *        'moderate' or 'strict' are allowed values.
     * @throws Zend_Gdata_App_InvalidArgumentException
     * @return Zend_Gdata_YouTube_VideoQuery Provides a fluent interface
     */
    public function setSafeSearch($value)
    {
        switch ($value) {
            case 'none':
                $this->_params['safeSearch'] = 'none';
                break;
            case 'moderate':
                $this->_params['safeSearch'] = 'moderate';
                break;
            case 'strict':
                $this->_params['safeSearch'] = 'strict';
                break;
            case null:
                unset($this->_params['safeSearch']);
            default:
                #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException(
                    'The safeSearch parameter only supports the values '.
                    '\'none\', \'moderate\' or \'strict\'.');
        }
    }

    /**
     * Return the value of the safeSearch parameter
     *
     * @return string|null The value of the safeSearch parameter if it has been
     *         set, null otherwise.
     */
    public function getSafeSearch()
    {
        if (array_key_exists('safeSearch', $this->_params)) {
            return $this->_params['safeSearch'];
        }
        return $this;
    }

    /**
     * Set the value of the orderby parameter
     *
     * @param string $value
     * @return Zend_Gdata_YouTube_Query Provides a fluent interface
     */
    public function setOrderBy($value)
    {
        if ($value != null) {
            $this->_params['orderby'] = $value;
        } else {
            unset($this->_params['orderby']);
        }
        return $this;
    }

    /**
     * Return the value of the format parameter
     *
     * @return string|null The value of format if it exists, null otherwise.
     */
    public function getFormat()
    {
        if (array_key_exists('format', $this->_params)) {
            return $this->_params['format'];
        } else {
            return null;
        }
    }

    /**
     * Return the value of the video query that has been set
     *
     * @return string|null The value of the video query if it exists,
     *         null otherwise.
     */
    public function getVideoQuery()
    {
        if (array_key_exists('vq', $this->_params)) {
            return $this->_params['vq'];
        } else {
            return null;
        }
    }

    /**
     * Return the value of the time parameter
     *
     * @return string|null The time parameter if it exists, null otherwise.
     */
    public function getTime()
    {
        if (array_key_exists('time', $this->_params)) {
            return $this->_params['time'];
        } else {
            return null;
        }
    }

    /**
     * Return the value of the orderby parameter if it exists
     *
     * @return string|null The value of orderby if it exists, null otherwise.
     */
    public function getOrderBy()
    {
        if (array_key_exists('orderby', $this->_params)) {
            return $this->_params['orderby'];
        } else {
            return null;
        }
    }

    /**
     * Generate the query string from the URL parameters, optionally modifying
     * them based on protocol version.
     *
     * @param integer $majorProtocolVersion The major protocol version
     * @param integer $minorProtocolVersion The minor protocol version
     * @throws Zend_Gdata_App_VersionException
     * @return string querystring
     */
    public function getQueryString($majorProtocolVersion = null,
        $minorProtocolVersion = null)
    {
        $queryArray = array();

        foreach ($this->_params as $name => $value) {
            if (substr($name, 0, 1) == '_') {
                continue;
            }

            switch($name) {
                case 'location-radius':
                    if ($majorProtocolVersion == 1) {
                        #require_once 'Zend/Gdata/App/VersionException.php';
                        throw new Zend_Gdata_App_VersionException("The $name " .
                            "parameter is only supported in version 2.");
                    }
                    break;

                case 'racy':
                    if ($majorProtocolVersion == 2) {
                        #require_once 'Zend/Gdata/App/VersionException.php';
                        throw new Zend_Gdata_App_VersionException("The $name " .
                            "parameter is not supported in version 2. " .
                            "Please use 'safeSearch'.");
                    }
                    break;

                case 'safeSearch':
                    if ($majorProtocolVersion == 1) {
                        #require_once 'Zend/Gdata/App/VersionException.php';
                        throw new Zend_Gdata_App_VersionException("The $name " .
                            "parameter is only supported in version 2. " .
                            "Please use 'racy'.");
                    }
                    break;

                case 'uploader':
                    if ($majorProtocolVersion == 1) {
                        #require_once 'Zend/Gdata/App/VersionException.php';
                        throw new Zend_Gdata_App_VersionException("The $name " .
                            "parameter is only supported in version 2.");
                    }
                    break;

                case 'vq':
                    if ($majorProtocolVersion == 2) {
                        $name = 'q';
                    }
                    break;
            }

            $queryArray[] = urlencode($name) . '=' . urlencode($value);

        }
        if (count($queryArray) > 0) {
            return '?' . implode('&', $queryArray);
        } else {
            return '';
        }
    }

    /**
     * Returns the generated full query URL, optionally modifying it based on
     * the protocol version.
     *
     * @param integer $majorProtocolVersion The major protocol version
     * @param integer $minorProtocolVersion The minor protocol version
     * @return string The URL
     */
    public function getQueryUrl($majorProtocolVersion = null,
        $minorProtocolVersion = null)
    {
        if (isset($this->_url)) {
            $url = $this->_url;
        } else {
            $url = Zend_Gdata_YouTube::VIDEO_URI;
        }
        if ($this->getCategory() !== null) {
            $url .= '/-/' . $this->getCategory();
        }
        $url = $url . $this->getQueryString($majorProtocolVersion,
            $minorProtocolVersion);
        return $url;
    }

}
