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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Gapps_Query
 */
#require_once('Zend/Gdata/Gapps/Query.php');

/**
 * Assists in constructing queries for user entries.
 * Instances of this class can be provided in many places where a URL is
 * required.
 *
 * For information on submitting queries to a server, see the
 * service class, Zend_Gdata_Photos.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Photos
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Photos_UserQuery extends Zend_Gdata_Query
{

    /**
     * Indicates the format of data returned in Atom feeds. Can be either
     * 'api' or 'base'. Default value is 'api'.
     *
     * @var string
     */
    protected $_projection = 'api';

    /**
     * Indicates whether to request a feed or entry in queries. Default
     * value is 'feed';
     *
     * @var string
     */
    protected $_type = 'feed';

    /**
     * A string which, if not null, indicates which user should
     * be retrieved by this query. If null, the default user will be used
     * instead.
     *
     * @var string
     */
    protected $_user = Zend_Gdata_Photos::DEFAULT_USER;

    /**
     * Create a new Query object with default values.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Set's the format of data returned in Atom feeds. Can be either
     * 'api' or 'base'. Normally, 'api' will be desired. Default is 'api'.
     *
     * @param string $value
     * @return Zend_Gdata_Photos_UserQuery Provides a fluent interface
     */
    public function setProjection($value)
    {
        $this->_projection = $value;
        return $this;
    }

    /**
     * Gets the format of data in returned in Atom feeds.
     *
     * @see setProjection
     * @return string projection
     */
    public function getProjection()
    {
        return $this->_projection;
    }

    /**
     * Set's the type of data returned in queries. Can be either
     * 'feed' or 'entry'. Normally, 'feed' will be desired. Default is 'feed'.
     *
     * @param string $value
     * @return Zend_Gdata_Photos_UserQuery Provides a fluent interface
     */
    public function setType($value)
    {
        $this->_type = $value;
        return $this;
    }

    /**
     * Gets the type of data in returned in queries.
     *
     * @see setType
     * @return string type
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set the user to query for. When set, this user's feed will be
     * returned. If not set or null, the default user's feed will be returned
     * instead.
     *
     * @param string $value The user to retrieve, or null for the default
     *          user.
     */
     public function setUser($value)
     {
         if ($value !== null) {
             $this->_user = $value;
         } else {
             $this->_user = Zend_Gdata_Photos::DEFAULT_USER;
         }
     }

    /**
     * Get the user which is to be returned.
     *
     * @see setUser
     * @return string The visibility to retrieve.
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * Set the visibility filter for entries returned. Only entries which
     * match this value will be returned. If null or unset, the default
     * value will be used instead.
     *
     * Valid values are 'all' (default), 'public', and 'private'.
     *
     * @param string $value The visibility to filter by, or null to use the
     *          default value.
     */
     public function setAccess($value)
     {
         if ($value !== null) {
             $this->_params['access'] = $value;
         } else {
             unset($this->_params['access']);
         }
     }

    /**
     * Get the visibility filter for entries returned.
     *
     * @see setAccess
     * @return string The visibility to filter by, or null for the default
     *          user.
     */
    public function getAccess()
    {
        return $this->_params['access'];
    }

    /**
     * Set the tag for entries that are returned. Only entries which
     * match this value will be returned. If null or unset, this filter will
     * not be applied.
     *
     * See http://code.google.com/apis/picasaweb/reference.html#Parameters
     * for a list of valid values.
     *
     * @param string $value The tag to filter by, or null if no
     *          filter is to be applied.
     */
     public function setTag($value)
     {
         if ($value !== null) {
             $this->_params['tag'] = $value;
         } else {
             unset($this->_params['tag']);
         }
     }

    /**
     * Get the tag filter for entries returned.
     *
     * @see setTag
     * @return string The tag to filter by, or null if no filter
     *          is to be applied.
     */
    public function getTag()
    {
        return $this->_params['tag'];
    }

    /**
     * Set the kind of entries that are returned. Only entries which
     * match this value will be returned. If null or unset, this filter will
     * not be applied.
     *
     * See http://code.google.com/apis/picasaweb/reference.html#Parameters
     * for a list of valid values.
     *
     * @param string $value The kind to filter by, or null if no
     *          filter is to be applied.
     */
     public function setKind($value)
     {
         if ($value !== null) {
             $this->_params['kind'] = $value;
         } else {
             unset($this->_params['kind']);
         }
     }

    /**
     * Get the kind of entries to be returned.
     *
     * @see setKind
     * @return string The kind to filter by, or null if no filter
     *          is to be applied.
     */
    public function getKind()
    {
        return $this->_params['kind'];
    }

    /**
     * Set the maximum image size for entries returned. Only entries which
     * match this value will be returned. If null or unset, this filter will
     * not be applied.
     *
     * See http://code.google.com/apis/picasaweb/reference.html#Parameters
     * for a list of valid values.
     *
     * @param string $value The image size to filter by, or null if no
     *          filter is to be applied.
     */
     public function setImgMax($value)
     {
         if ($value !== null) {
             $this->_params['imgmax'] = $value;
         } else {
             unset($this->_params['imgmax']);
         }
     }

    /**
     * Get the maximum image size filter for entries returned.
     *
     * @see setImgMax
     * @return string The image size size to filter by, or null if no filter
     *          is to be applied.
     */
    public function getImgMax()
    {
        return $this->_params['imgmax'];
    }

    /**
     * Set the thumbnail size filter for entries returned. Only entries which
     * match this value will be returned. If null or unset, this filter will
     * not be applied.
     *
     * See http://code.google.com/apis/picasaweb/reference.html#Parameters
     * for a list of valid values.
     *
     * @param string $value The thumbnail size to filter by, or null if no
     *          filter is to be applied.
     */
     public function setThumbsize($value)
     {
         if ($value !== null) {
             $this->_params['thumbsize'] = $value;
         } else {
             unset($this->_params['thumbsize']);
         }
     }

    /**
     * Get the thumbnail size filter for entries returned.
     *
     * @see setThumbsize
     * @return string The thumbnail size to filter by, or null if no filter
     *          is to be applied.
     */
    public function getThumbsize()
    {
        return $this->_params['thumbsize'];
    }

    /**
     * Returns the URL generated for this query, based on it's current
     * parameters.
     *
     * @return string A URL generated based on the state of this query.
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function getQueryUrl($incomingUri = null)
    {
        $uri = Zend_Gdata_Photos::PICASA_BASE_URI;

        if ($this->getType() !== null) {
            $uri .= '/' . $this->getType();
        } else {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Type must be feed or entry, not null');
        }

        if ($this->getProjection() !== null) {
            $uri .= '/' . $this->getProjection();
        } else {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'Projection must not be null');
        }

        if ($this->getUser() !== null) {
            $uri .= '/user/' . $this->getUser();
        } else {
            // Should never occur due to setter behavior
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'User must not be null');
        }

        $uri .= $incomingUri;
        $uri .= $this->getQueryString();
        return $uri;
    }

}
