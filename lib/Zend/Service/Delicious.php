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
 * @subpackage Delicious
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Delicious.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Rest_Client
 */
#require_once 'Zend/Rest/Client.php';

/**
 * @see Zend_Json_Decoder
 */
#require_once 'Zend/Json/Decoder.php';

/**
 * @see Zend_Service_Delicious_SimplePost
 */
#require_once 'Zend/Service/Delicious/SimplePost.php';

/**
 * @see Zend_Service_Delicious_Post
 */
#require_once 'Zend/Service/Delicious/Post.php';

/**
 * @see Zend_Service_Delicious_PostList
 */
#require_once 'Zend/Service/Delicious/PostList.php';


/**
 * Zend_Service_Delicious is a concrete implementation of the del.icio.us web service
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Delicious
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Delicious
{
    const API_URI = 'https://api.del.icio.us';

    const PATH_UPDATE        = '/v1/posts/update';
    const PATH_TAGS          = '/v1/tags/get';
    const PATH_TAG_RENAME    = '/v1/tags/rename';
    const PATH_BUNDLES       = '/v1/tags/bundles/all';
    const PATH_BUNDLE_DELETE = '/v1/tags/bundles/delete';
    const PATH_BUNDLE_ADD    = '/v1/tags/bundles/set';
    const PATH_DATES         = '/v1/posts/dates';
    const PATH_POST_DELETE   = '/v1/posts/delete';
    const PATH_POSTS_GET     = '/v1/posts/get';
    const PATH_POSTS_ALL     = '/v1/posts/all';
    const PATH_POSTS_ADD     = '/v1/posts/add';
    const PATH_POSTS_RECENT  = '/v1/posts/recent';

    const JSON_URI     = 'http://del.icio.us';
    const JSON_POSTS   = '/feeds/json/%s/%s';
    const JSON_TAGS    = '/feeds/json/tags/%s';
    const JSON_NETWORK = '/feeds/json/network/%s';
    const JSON_FANS    = '/feeds/json/fans/%s';
    const JSON_URL     = '/feeds/json/url/data';

    /**
     * Zend_Service_Rest instance
     *
     * @var Zend_Service_Rest
     */
    protected $_rest;

    /**
     * Username
     *
     * @var string
     */
    protected $_authUname;

    /**
     * Password
     *
     * @var string
     */
    protected $_authPass;

    /**
     * Microtime of last request
     *
     * @var float
     */
    protected static $_lastRequestTime = 0;

    /**
     * Constructs a new del.icio.us Web Services Client
     *
     * @param  string $uname Client username
     * @param  string $pass  Client password
     * @return void
     */
    public function __construct($uname = null, $pass = null)
    {
        $this->_rest = new Zend_Rest_Client();
        $this->_rest->getHttpClient()->setConfig(array('ssltransport' => 'ssl'));
        $this->setAuth($uname, $pass);
    }

    /**
     * Set client username and password
     *
     * @param  string $uname Client user name
     * @param  string $pass  Client password
     * @return Zend_Service_Delicious Provides a fluent interface
     */
    public function setAuth($uname, $pass)
    {
        $this->_authUname = $uname;
        $this->_authPass  = $pass;

        return $this;
    }

    /**
     * Get time of the last update
     *
     * @throws Zend_Service_Delicious_Exception
     * @return Zend_Date
     */
    public function getLastUpdate()
    {
        $response = $this->makeRequest(self::PATH_UPDATE);

        $rootNode = $response->documentElement;
        if ($rootNode && $rootNode->nodeName == 'update') {
            /**
             * @todo replace strtotime() with Zend_Date equivalent
             */
            return new Zend_Date(strtotime($rootNode->getAttribute('time')));
        } else {
            /**
             * @see Zend_Service_Delicious_Exception
             */
            #require_once 'Zend/Service/Delicious/Exception.php';
            throw new Zend_Service_Delicious_Exception('del.icio.us web service has returned something odd!');
        }
    }

    /**
     * Get all tags, returning an array with tags as keys and number of corresponding posts as values
     *
     * @return array list of tags
     */
    public function getTags()
    {
        $response = $this->makeRequest(self::PATH_TAGS);

        return self::_xmlResponseToArray($response, 'tags', 'tag', 'tag', 'count');
    }

    /**
     * Rename a tag
     *
     * @param  string $old Old tag name
     * @param  string $new New tag name
     * @return Zend_Service_Delicious Provides a fluent interface
     */
    public function renameTag($old, $new)
    {
        $response = $this->makeRequest(self::PATH_TAG_RENAME, array('old' => $old, 'new' => $new));

        self::_evalXmlResult($response);

        return $this;
    }

    /**
     * Get all bundles, returning an array with bundles as keys and array of tags as values
     *
     * @return array list of bundles
     */
    public function getBundles()
    {
        $response = $this->makeRequest(self::PATH_BUNDLES);

        $bundles = self::_xmlResponseToArray($response, 'bundles', 'bundle', 'name', 'tags');
        foreach ($bundles as &$tags) {
            $tags = explode(' ', $tags);
        }
        return $bundles;
    }

    /**
     * Adds a new bundle
     *
     * @param  string $bundle Name of new bundle
     * @param  array  $tags   Array of tags
     * @return Zend_Service_Delicious Provides a fluent interface
     */
    public function addBundle($bundle, array $tags)
    {
        $tags = implode(' ', (array) $tags);
        $response = $this->makeRequest(self::PATH_BUNDLE_ADD, array('bundle' => $bundle, 'tags' => $tags));

        self::_evalXmlResult($response);

        return $this;
    }

    /**
     * Delete a bundle
     *
     * @param  string $bundle Name of bundle to be deleted
     * @return Zend_Service_Delicious Provides a fluent interface
     */
    public function deleteBundle($bundle)
    {
        $response = $this->makeRequest(self::PATH_BUNDLE_DELETE, array('bundle' => $bundle));

        self::_evalXmlResult($response);

        return $this;
    }

    /**
     * Delete a post
     *
     * @param  string $url URL of post to be deleted
     * @return Zend_Service_Delicious Provides a fluent interface
     */
    public function deletePost($url)
    {
        $response = $this->makeRequest(self::PATH_POST_DELETE, array('url' => $url));

        self::_evalXmlResult($response);

        return $this;
    }

    /**
     * Get number of posts by date
     *
     * Returns array where keys are dates and values are numbers of posts
     *
     * @param  string $tag Optional filtering by tag
     * @return array list of dates
     */
    public function getDates($tag = null)
    {
        $parms = array();
        if ($tag) {
            $parms['tag'] = $tag;
        }

        $response = $this->makeRequest(self::PATH_DATES, $parms);

        return self::_xmlResponseToArray($response, 'dates', 'date', 'date', 'count');
    }

    /**
     * Get posts matching the arguments
     *
     * If no date or url is given, most recent date will be used
     *
     * @param  string    $tag Optional filtering by tag
     * @param  Zend_Date $dt  Optional filtering by date
     * @param  string    $url Optional filtering by url
     * @throws Zend_Service_Delicious_Exception
     * @return Zend_Service_Delicious_PostList
     */
    public function getPosts($tag = null, Zend_Date $dt = null, $url = null)
    {
        $parms = array();
        if ($tag) {
            $parms['tag'] = $tag;
        }
        if ($url) {
            $parms['url'] = $url;
        }
        if ($dt) {
            $parms['dt'] = $dt->get('Y-m-d\TH:i:s\Z');
        }

        $response = $this->makeRequest(self::PATH_POSTS_GET, $parms);

        return $this->_parseXmlPostList($response);
    }

    /**
     * Get all posts
     *
     * @param  string $tag Optional filtering by tag
     * @return Zend_Service_Delicious_PostList
     */
    public function getAllPosts($tag = null)
    {
        $parms = array();
        if ($tag) {
            $parms['tag'] = $tag;
        }

        $response = $this->makeRequest(self::PATH_POSTS_ALL, $parms);

        return $this->_parseXmlPostList($response);
    }

    /**
     * Get recent posts
     *
     * @param  string $tag   Optional filtering by tag
     * @param  string $count Maximum number of posts to be returned (default 15)
     * @return Zend_Service_Delicious_PostList
     */
    public function getRecentPosts($tag = null, $count = 15)
    {
        $parms = array();
        if ($tag) {
            $parms['tag'] = $tag;
        }
        if ($count) {
            $parms['count'] = $count;
        }

        $response = $this->makeRequest(self::PATH_POSTS_RECENT, $parms);

        return $this->_parseXmlPostList($response);
    }

    /**
     * Create new post
     *
     * @return Zend_Service_Delicious_Post
     */
    public function createNewPost($title, $url)
    {
        return new Zend_Service_Delicious_Post($this, array('title' => $title, 'url' => $url));
    }

    /**
     * Get posts of a user
     *
     * @param  string $user  Owner of the posts
     * @param  int    $count Number of posts (default 15, max. 100)
     * @param  string $tag   Optional filtering by tag
     * @return Zend_Service_Delicious_PostList
     */
    public function getUserPosts($user, $count = null, $tag = null)
    {
        $parms = array();
        if ($count) {
            $parms['count'] = $count;
        }

        $path = sprintf(self::JSON_POSTS, $user, $tag);
        $res = $this->makeRequest($path, $parms, 'json');

        return new Zend_Service_Delicious_PostList($this, $res);
    }

    /**
     * Get tags of a user
     *
     * Returned array has tags as keys and number of posts as values
     *
     * @param  string $user    Owner of the posts
     * @param  int    $atleast Include only tags for which there are at least ### number of posts
     * @param  int    $count   Number of tags to get (default all)
     * @param  string $sort    Order of returned tags ('alpha' || 'count')
     * @return array
     */
    public function getUserTags($user, $atleast = null, $count = null, $sort = 'alpha')
    {
        $parms = array();
        if ($atleast) {
            $parms['atleast'] = $atleast;
        }
        if ($count) {
            $parms['count'] = $count;
        }
        if ($sort) {
            $parms['sort'] = $sort;
        }

        $path = sprintf(self::JSON_TAGS, $user);

        return $this->makeRequest($path, $parms, 'json');
    }

    /**
     * Get network of a user
     *
     * @param  string $user Owner of the network
     * @return array
     */
    public function getUserNetwork($user)
    {
        $path = sprintf(self::JSON_NETWORK, $user);
        return $this->makeRequest($path, array(), 'json');
    }

    /**
     * Get fans of a user
     *
     * @param  string $user Owner of the fans
     * @return array
     */
    public function getUserFans($user)
    {
        $path = sprintf(self::JSON_FANS, $user);
        return $this->makeRequest($path, array(), 'json');
    }

    /**
     * Get details on a particular bookmarked URL
     *
     * Returned array contains four elements:
     *  - hash - md5 hash of URL
     *  - top_tags - array of tags and their respective usage counts
     *  - url - URL for which details were returned
     *  - total_posts - number of users that have bookmarked URL
     *
     * If URL hasen't been bookmarked null is returned.
     *
     * @param  string $url URL for which to get details
     * @return array
     */
    public function getUrlDetails($url)
    {
        $parms = array('hash' => md5($url));

        $res = $this->makeRequest(self::JSON_URL, $parms, 'json');

        if(isset($res[0])) {
            return $res[0];
        } else {
            return null;
        }
    }

    /**
     * Handles all GET requests to a web service
     *
     * @param   string $path  Path
     * @param   array  $parms Array of GET parameters
     * @param   string $type  Type of a request ("xml"|"json")
     * @return  mixed  decoded response from web service
     * @throws  Zend_Service_Delicious_Exception
     */
    public function makeRequest($path, array $parms = array(), $type = 'xml')
    {
        // if previous request was made less then 1 sec ago
        // wait until we can make a new request
        $timeDiff = microtime(true) - self::$_lastRequestTime;
        if ($timeDiff < 1) {
            usleep((1 - $timeDiff) * 1000000);
        }

        $this->_rest->getHttpClient()->setAuth($this->_authUname, $this->_authPass);

        switch ($type) {
            case 'xml':
                $this->_rest->setUri(self::API_URI);
                break;
            case 'json':
                $parms['raw'] = true;
                $this->_rest->setUri(self::JSON_URI);
                break;
            default:
                /**
                 * @see Zend_Service_Delicious_Exception
                 */
                #require_once 'Zend/Service/Delicious/Exception.php';
                throw new Zend_Service_Delicious_Exception('Unknown request type');
        }

        self::$_lastRequestTime = microtime(true);
        $response = $this->_rest->restGet($path, $parms);

        if (!$response->isSuccessful()) {
            /**
             * @see Zend_Service_Delicious_Exception
             */
            #require_once 'Zend/Service/Delicious/Exception.php';
            throw new Zend_Service_Delicious_Exception("Http client reported an error: '{$response->getMessage()}'");
        }

        $responseBody = $response->getBody();

        switch ($type) {
            case 'xml':
                $dom = new DOMDocument() ;

                if (!@$dom->loadXML($responseBody)) {
                    /**
                     * @see Zend_Service_Delicious_Exception
                     */
                    #require_once 'Zend/Service/Delicious/Exception.php';
                    throw new Zend_Service_Delicious_Exception('XML Error');
                }

                return $dom;
            case 'json':
                return Zend_Json_Decoder::decode($responseBody);
        }
    }

    /**
     * Transform XML string to array
     *
     * @param   DOMDocument $response
     * @param   string      $root     Name of root tag
     * @param   string      $child    Name of children tags
     * @param   string      $attKey   Attribute of child tag to be used as a key
     * @param   string      $attValue Attribute of child tag to be used as a value
     * @return  array
     * @throws  Zend_Service_Delicious_Exception
     */
    private static function _xmlResponseToArray(DOMDocument $response, $root, $child, $attKey, $attValue)
    {
        $rootNode = $response->documentElement;
        $arrOut = array();

        if ($rootNode->nodeName == $root) {
            $childNodes = $rootNode->childNodes;

            for ($i = 0; $i < $childNodes->length; $i++) {
                $currentNode = $childNodes->item($i);
                if ($currentNode->nodeName == $child) {
                    $arrOut[$currentNode->getAttribute($attKey)] = $currentNode->getAttribute($attValue);
                }
            }
        } else {
            /**
             * @see Zend_Service_Delicious_Exception
             */
            #require_once 'Zend/Service/Delicious/Exception.php';
            throw new Zend_Service_Delicious_Exception('del.icio.us web service has returned something odd!');
        }

        return $arrOut;
    }

    /**
     * Constructs Zend_Service_Delicious_PostList from XML response
     *
     * @param   DOMDocument $response
     * @return  Zend_Service_Delicious_PostList
     * @throws  Zend_Service_Delicious_Exception
     */
    private function _parseXmlPostList(DOMDocument $response)
    {
        $rootNode = $response->documentElement;

        if ($rootNode->nodeName == 'posts') {
            return new Zend_Service_Delicious_PostList($this, $rootNode->childNodes);
        } else {
            /**
             * @see Zend_Service_Delicious_Exception
             */
            #require_once 'Zend/Service/Delicious/Exception.php';
            throw new Zend_Service_Delicious_Exception('del.icio.us web service has returned something odd!');
        }
    }

    /**
     * Evaluates XML response
     *
     * @param   DOMDocument $response
     * @return  void
     * @throws  Zend_Service_Delicious_Exception
     */
    private static function _evalXmlResult(DOMDocument $response)
    {
        $rootNode = $response->documentElement;

        if ($rootNode && $rootNode->nodeName == 'result') {

            if ($rootNode->hasAttribute('code')) {
                $strResponse = $rootNode->getAttribute('code');
            } else {
                $strResponse = $rootNode->nodeValue;
            }

            if ($strResponse != 'done' && $strResponse != 'ok') {
                /**
                 * @see Zend_Service_Delicious_Exception
                 */
                #require_once 'Zend/Service/Delicious/Exception.php';
                throw new Zend_Service_Delicious_Exception("del.icio.us web service: '{$strResponse}'");
            }
        } else {
            /**
             * @see Zend_Service_Delicious_Exception
             */
            #require_once 'Zend/Service/Delicious/Exception.php';
            throw new Zend_Service_Delicious_Exception('del.icio.us web service has returned something odd!');
        }
    }
}
