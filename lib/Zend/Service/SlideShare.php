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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: SlideShare.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Zend_Http_Client
 */
#require_once 'Zend/Http/Client.php';

/**
 * Zend_Cache
 */
#require_once 'Zend/Cache.php';

/**
 * Zend_Service_SlideShare_SlideShow
 */
#require_once 'Zend/Service/SlideShare/SlideShow.php';

/**
 * The Zend_Service_SlideShare component is used to interface with the
 * slideshare.net web server to retrieve slide shows hosted on the web site for
 * display or other processing.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage SlideShare
 * @throws     Zend_Service_SlideShare_Exception
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_SlideShare
{

    /**
     * Web service result code mapping
     */
    const SERVICE_ERROR_BAD_APIKEY       = 1;
    const SERVICE_ERROR_BAD_AUTH         = 2;
    const SERVICE_ERROR_MISSING_TITLE    = 3;
    const SERVICE_ERROR_MISSING_FILE     = 4;
    const SERVICE_ERROR_EMPTY_TITLE      = 5;
    const SERVICE_ERROR_NOT_SOURCEOBJ    = 6;
    const SERVICE_ERROR_INVALID_EXT      = 7;
    const SERVICE_ERROR_FILE_TOO_BIG     = 8;
    const SERVICE_ERROR_SHOW_NOT_FOUND   = 9;
    const SERVICE_ERROR_USER_NOT_FOUND   = 10;
    const SERVICE_ERROR_GROUP_NOT_FOUND  = 11;
    const SERVICE_ERROR_MISSING_TAG      = 12;
    const SERVICE_ERROR_DAILY_LIMIT      = 99;
    const SERVICE_ERROR_ACCOUNT_BLOCKED  = 100;

    /**
     * Slide share Web service communication URIs
     */
    const SERVICE_UPLOAD_URI                  = 'http://www.slideshare.net/api/1/upload_slideshow';
    const SERVICE_GET_SHOW_URI                = 'http://www.slideshare.net/api/1/get_slideshow';
    const SERVICE_GET_SHOW_BY_USER_URI        = 'http://www.slideshare.net/api/1/get_slideshow_by_user';
    const SERVICE_GET_SHOW_BY_TAG_URI         = 'http://www.slideshare.net/api/1/get_slideshow_by_tag';
    const SERVICE_GET_SHOW_BY_GROUP_URI       = 'http://www.slideshare.net/api/1/get_slideshows_from_group';

    /**
     * The MIME type of Slideshow files
     *
     */
    const POWERPOINT_MIME_TYPE    = "application/vnd.ms-powerpoint";

    /**
     * The API key to use in requests
     *
     * @var string The API key
     */
    protected $_apiKey;

    /**
     * The shared secret to use in requests
     *
     * @var string the Shared secret
     */
    protected $_sharedSecret;

    /**
     * The username to use in requests
     *
     * @var string the username
     */
    protected $_username;

    /**
     * The password to use in requests
     *
     * @var string the password
     */
    protected $_password;

    /**
     * The HTTP Client object to use to perform requests
     *
     * @var Zend_Http_Client
     */
    protected $_httpclient;

    /**
     * The Cache object to use to perform caching
     *
     * @var Zend_Cache_Core
     */
    protected $_cacheobject;

    /**
     * Sets the Zend_Http_Client object to use in requests. If not provided a default will
     * be used.
     *
     * @param Zend_Http_Client $client The HTTP client instance to use
     * @return Zend_Service_SlideShare
     */
    public function setHttpClient(Zend_Http_Client $client)
    {
        $this->_httpclient = $client;
        return $this;
    }

    /**
     * Returns the instance of the Zend_Http_Client which will be used. Creates an instance
     * of Zend_Http_Client if no previous client was set.
     *
     * @return Zend_Http_Client The HTTP client which will be used
     */
    public function getHttpClient()
    {

        if(!($this->_httpclient instanceof Zend_Http_Client)) {
            $client = new Zend_Http_Client();
            $client->setConfig(array('maxredirects' => 2,
                                     'timeout' => 5));

            $this->setHttpClient($client);
        }

        $this->_httpclient->resetParameters();
        return $this->_httpclient;
    }

    /**
     * Sets the Zend_Cache object to use to cache the results of API queries
     *
     * @param Zend_Cache_Core $cacheobject The Zend_Cache object used
     * @return Zend_Service_SlideShare
     */
    public function setCacheObject(Zend_Cache_Core $cacheobject)
    {
        $this->_cacheobject = $cacheobject;
        return $this;
    }

    /**
     * Gets the Zend_Cache object which will be used to cache API queries. If no cache object
     * was previously set the the default will be used (Filesystem caching in /tmp with a life
     * time of 43200 seconds)
     *
     * @return Zend_Cache_Core The object used in caching
     */
    public function getCacheObject()
    {

        if(!($this->_cacheobject instanceof Zend_Cache_Core)) {
            $cache = Zend_Cache::factory('Core', 'File', array('lifetime' => 43200,
                                                               'automatic_serialization' => true),
                                                         array('cache_dir' => '/tmp'));

            $this->setCacheObject($cache);
        }

        return $this->_cacheobject;
    }

    /**
     * Returns the user name used for API calls
     *
     * @return string The username
     */
    public function getUserName()
    {
        return $this->_username;
    }

    /**
     * Sets the user name to use for API calls
     *
     * @param string $un The username to use
     * @return Zend_Service_SlideShare
     */
    public function setUserName($un)
    {
        $this->_username = $un;
        return $this;
    }

    /**
     * Gets the password to use in API calls
     *
     * @return string the password to use in API calls
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Sets the password to use in API calls
     *
     * @param string $pw The password to use
     * @return Zend_Service_SlideShare
     */
    public function setPassword($pw)
    {
        $this->_password = (string)$pw;
        return $this;
    }

    /**
     * Gets the API key to be used in making API calls
     *
     * @return string the API Key
     */
    public function getApiKey()
    {
        return $this->_apiKey;
    }

    /**
     * Sets the API key to be used in making API calls
     *
     * @param string $key The API key to use
     * @return Zend_Service_SlideShare
     */
    public function setApiKey($key)
    {
        $this->_apiKey = (string)$key;
        return $this;
    }

    /**
     * Gets the shared secret used in making API calls
     *
     * @return string the Shared secret
     */
    public function getSharedSecret()
    {
        return $this->_sharedSecret;
    }

    /**
     * Sets the shared secret used in making API calls
     *
     * @param string $secret the shared secret
     * @return Zend_Service_SlideShare
     */
    public function setSharedSecret($secret)
    {
        $this->_sharedSecret = (string)$secret;
        return $this;
    }

    /**
     * The Constructor
     *
     * @param string $apikey The API key
     * @param string $sharedSecret The shared secret
     * @param string $username The username
     * @param string $password The password
     */
    public function __construct($apikey, $sharedSecret, $username = null, $password = null)
    {
        $this->setApiKey($apikey)
             ->setSharedSecret($sharedSecret)
             ->setUserName($username)
             ->setPassword($password);

        $this->_httpclient = new Zend_Http_Client();
    }

    /**
     * Uploads the specified Slide show the the server
     *
     * @param Zend_Service_SlideShare_SlideShow $ss The slide show object representing the slide show to upload
     * @param boolean $make_src_public Determines if the the slide show's source file is public or not upon upload
     * @return Zend_Service_SlideShare_SlideShow The passed Slide show object, with the new assigned ID provided
     */
    public function uploadSlideShow(Zend_Service_SlideShare_SlideShow $ss, $make_src_public = true)
    {

        $timestamp = time();

        $params = array('api_key' => $this->getApiKey(),
                        'ts' => $timestamp,
                        'hash' => sha1($this->getSharedSecret().$timestamp),
                        'username' => $this->getUserName(),
                        'password' => $this->getPassword(),
                        'slideshow_title' => $ss->getTitle());

        $description = $ss->getDescription();
        $tags = $ss->getTags();

        $filename = $ss->getFilename();

        if(!file_exists($filename) || !is_readable($filename)) {
            #require_once 'Zend/Service/SlideShare/Exception.php';
            throw new Zend_Service_SlideShare_Exception("Specified Slideshow for upload not found or unreadable");
        }

        if(!empty($description)) {
            $params['slideshow_description'] = $description;
        } else {
            $params['slideshow_description'] = "";
        }

        if(!empty($tags)) {
            $tmp = array();
            foreach($tags as $tag) {
                $tmp[] = "\"$tag\"";
            }
            $params['slideshow_tags'] = implode(' ', $tmp);
        } else {
            $params['slideshow_tags'] = "";
        }


        $client = $this->getHttpClient();
        $client->setUri(self::SERVICE_UPLOAD_URI);
        $client->setParameterPost($params);
        $client->setFileUpload($filename, "slideshow_srcfile");

        #require_once 'Zend/Http/Client/Exception.php';
        try {
            $response = $client->request('POST');
        } catch(Zend_Http_Client_Exception $e) {
            #require_once 'Zend/Service/SlideShare/Exception.php';
            throw new Zend_Service_SlideShare_Exception("Service Request Failed: {$e->getMessage()}", 0, $e);
        }

        $sxe = simplexml_load_string($response->getBody());

        if($sxe->getName() == "SlideShareServiceError") {
            $message = (string)$sxe->Message[0];
            list($code, $error_str) = explode(':', $message);
            #require_once 'Zend/Service/SlideShare/Exception.php';
            throw new Zend_Service_SlideShare_Exception(trim($error_str), $code);
        }

        if(!$sxe->getName() == "SlideShowUploaded") {
            #require_once 'Zend/Service/SlideShare/Exception.php';
            throw new Zend_Service_SlideShare_Exception("Unknown XML Respons Received");
        }

        $ss->setId((int)(string)$sxe->SlideShowID);

        return $ss;
    }

    /**
     * Retrieves a slide show's information based on slide show ID
     *
     * @param int $ss_id The slide show ID
     * @return Zend_Service_SlideShare_SlideShow the Slideshow object
     */
    public function getSlideShow($ss_id)
    {
        $timestamp = time();

        $params = array('api_key' => $this->getApiKey(),
                        'ts' => $timestamp,
                        'hash' => sha1($this->getSharedSecret().$timestamp),
                        'slideshow_id' => $ss_id);

        $cache = $this->getCacheObject();

        $cache_key = md5("__zendslideshare_cache_$ss_id");

        if(!$retval = $cache->load($cache_key)) {
            $client = $this->getHttpClient();

            $client->setUri(self::SERVICE_GET_SHOW_URI);
            $client->setParameterPost($params);

            #require_once 'Zend/Http/Client/Exception.php';
            try {
                $response = $client->request('POST');
            } catch(Zend_Http_Client_Exception $e) {
                #require_once 'Zend/Service/SlideShare/Exception.php';
                throw new Zend_Service_SlideShare_Exception("Service Request Failed: {$e->getMessage()}", 0, $e);
            }

            $sxe = simplexml_load_string($response->getBody());

            if($sxe->getName() == "SlideShareServiceError") {
                $message = (string)$sxe->Message[0];
                list($code, $error_str) = explode(':', $message);
                #require_once 'Zend/Service/SlideShare/Exception.php';
                throw new Zend_Service_SlideShare_Exception(trim($error_str), $code);
            }

            if(!$sxe->getName() == 'Slideshows') {
                #require_once 'Zend/Service/SlideShare/Exception.php';
                throw new Zend_Service_SlideShare_Exception('Unknown XML Repsonse Received');
            }

            $retval = $this->_slideShowNodeToObject(clone $sxe->Slideshow[0]);

            $cache->save($retval, $cache_key);
        }

        return $retval;
    }

    /**
     * Retrieves an array of slide shows for a given username
     *
     * @param string $username The username to retrieve slide shows from
     * @param int $offset The offset of the list to start retrieving from
     * @param int $limit The maximum number of slide shows to retrieve
     * @return array An array of Zend_Service_SlideShare_SlideShow objects
     */
    public function getSlideShowsByUsername($username, $offset = null, $limit = null)
    {
        return $this->_getSlideShowsByType('username_for', $username, $offset, $limit);
    }

    /**
     * Retrieves an array of slide shows based on tag
     *
     * @param string $tag The tag to retrieve slide shows with
     * @param int $offset The offset of the list to start retrieving from
     * @param int $limit The maximum number of slide shows to retrieve
     * @return array An array of Zend_Service_SlideShare_SlideShow objects
     */
    public function getSlideShowsByTag($tag, $offset = null, $limit = null)
    {

        if(is_array($tag)) {
            $tmp = array();
            foreach($tag as $t) {
                $tmp[] = "\"$t\"";
            }

            $tag = implode(" ", $tmp);
        }

        return $this->_getSlideShowsByType('tag', $tag, $offset, $limit);
    }

    /**
     * Retrieves an array of slide shows based on group name
     *
     * @param string $group The group name to retrieve slide shows for
     * @param int $offset The offset of the list to start retrieving from
     * @param int $limit The maximum number of slide shows to retrieve
     * @return array An array of Zend_Service_SlideShare_SlideShow objects
     */
    public function getSlideShowsByGroup($group, $offset = null, $limit = null)
    {
        return $this->_getSlideShowsByType('group_name', $group, $offset, $limit);
    }

    /**
     * Retrieves Zend_Service_SlideShare_SlideShow object arrays based on the type of
     * list desired
     *
     * @param string $key The type of slide show object to retrieve
     * @param string $value The specific search query for the slide show type to look up
     * @param int $offset The offset of the list to start retrieving from
     * @param int $limit The maximum number of slide shows to retrieve
     * @return array An array of Zend_Service_SlideShare_SlideShow objects
     */
    protected function _getSlideShowsByType($key, $value, $offset = null, $limit = null)
    {

        $key = strtolower($key);

        switch($key) {
            case 'username_for':
                $responseTag = 'User';
                $queryUri = self::SERVICE_GET_SHOW_BY_USER_URI;
                break;
            case 'group_name':
                $responseTag = 'Group';
                $queryUri = self::SERVICE_GET_SHOW_BY_GROUP_URI;
                break;
            case 'tag':
                $responseTag = 'Tag';
                $queryUri = self::SERVICE_GET_SHOW_BY_TAG_URI;
                break;
            default:
                #require_once 'Zend/Service/SlideShare/Exception.php';
                throw new Zend_Service_SlideShare_Exception("Invalid SlideShare Query");
        }

        $timestamp = time();

        $params = array('api_key' => $this->getApiKey(),
                        'ts' => $timestamp,
                        'hash' => sha1($this->getSharedSecret().$timestamp),
                        $key => $value);

        if($offset !== null) {
            $params['offset'] = (int)$offset;
        }

        if($limit !== null) {
            $params['limit'] = (int)$limit;
        }

        $cache = $this->getCacheObject();

        $cache_key = md5($key.$value.$offset.$limit);

        if(!$retval = $cache->load($cache_key)) {

            $client = $this->getHttpClient();

            $client->setUri($queryUri);
            $client->setParameterPost($params);

            #require_once 'Zend/Http/Client/Exception.php';
            try {
                $response = $client->request('POST');
            } catch(Zend_Http_Client_Exception $e) {
                #require_once 'Zend/Service/SlideShare/Exception.php';
                throw new Zend_Service_SlideShare_Exception("Service Request Failed: {$e->getMessage()}", 0, $e);
            }

            $sxe = simplexml_load_string($response->getBody());

            if($sxe->getName() == "SlideShareServiceError") {
                $message = (string)$sxe->Message[0];
                list($code, $error_str) = explode(':', $message);
                #require_once 'Zend/Service/SlideShare/Exception.php';
                throw new Zend_Service_SlideShare_Exception(trim($error_str), $code);
            }

            if(!$sxe->getName() == $responseTag) {
                #require_once 'Zend/Service/SlideShare/Exception.php';
                throw new Zend_Service_SlideShare_Exception('Unknown or Invalid XML Repsonse Received');
            }

            $retval = array();

            foreach($sxe->children() as $node) {
                if($node->getName() == 'Slideshow') {
                    $retval[] = $this->_slideShowNodeToObject($node);
                }
            }

            $cache->save($retval, $cache_key);
        }

        return $retval;
    }

    /**
     * Converts a SimpleXMLElement object representing a response from the service
     * into a Zend_Service_SlideShare_SlideShow object
     *
     * @param SimpleXMLElement $node The input XML from the slideshare.net service
     * @return Zend_Service_SlideShare_SlideShow The resulting object
     */
    protected function _slideShowNodeToObject(SimpleXMLElement $node)
    {

        if($node->getName() == 'Slideshow') {

            $ss = new Zend_Service_SlideShare_SlideShow();

            $ss->setId((string)$node->ID);
            $ss->setDescription((string)$node->Description);
            $ss->setEmbedCode((string)$node->EmbedCode);
            $ss->setNumViews((string)$node->Views);
            $ss->setPermaLink((string)$node->Permalink);
            $ss->setStatus((string)$node->Status);
            $ss->setStatusDescription((string)$node->StatusDescription);

            foreach(explode(",", (string)$node->Tags) as $tag) {

                if(!in_array($tag, $ss->getTags())) {
                    $ss->addTag($tag);
                }
            }

            $ss->setThumbnailUrl((string)$node->Thumbnail);
            $ss->setTitle((string)$node->Title);
            $ss->setLocation((string)$node->Location);
            $ss->setTranscript((string)$node->Transcript);

            return $ss;

        }

        #require_once 'Zend/Service/SlideShare/Exception.php';
        throw new Zend_Service_SlideShare_Exception("Was not provided the expected XML Node for processing");
    }
}
