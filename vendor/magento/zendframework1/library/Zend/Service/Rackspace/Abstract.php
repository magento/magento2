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
 * @subpackage Rackspace
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Http/Client.php';

abstract class Zend_Service_Rackspace_Abstract
{
    const VERSION                = 'v1.0';
    const US_AUTH_URL            = 'https://auth.api.rackspacecloud.com';
    const UK_AUTH_URL            = 'https://lon.auth.api.rackspacecloud.com';
    const API_FORMAT             = 'json';
    const USER_AGENT             = 'Zend_Service_Rackspace';
    const STORAGE_URL            = "X-Storage-Url";
    const AUTHTOKEN              = "X-Auth-Token";
    const AUTHUSER_HEADER        = "X-Auth-User";
    const AUTHKEY_HEADER         = "X-Auth-Key";
    const AUTHUSER_HEADER_LEGACY = "X-Storage-User";
    const AUTHKEY_HEADER_LEGACY  = "X-Storage-Pass";
    const AUTHTOKEN_LEGACY       = "X-Storage-Token";
    const CDNM_URL               = "X-CDN-Management-Url";
    const MANAGEMENT_URL         = "X-Server-Management-Url";
    /**
     * Rackspace Key
     *
     * @var string
     */
    protected $key;
    /**
     * Rackspace account name
     *
     * @var string
     */
    protected $user;
    /**
     * Token of authentication
     *
     * @var string
     */
    protected $token;
    /**
     * Authentication URL
     *
     * @var string
     */
    protected $authUrl;
    /**
     * @var Zend_Http_Client
     */
    protected $httpClient;
    /**
     * Error Msg
     *
     * @var string
     */
    protected $errorMsg;
    /**
     * HTTP error code
     *
     * @var string
     */
    protected $errorCode;
    /**
     * Storage URL
     *
     * @var string
     */
    protected $storageUrl;
    /**
     * CDN URL
     *
     * @var string
     */
    protected $cdnUrl;
    /**
     * Server management URL
     *
     * @var string
     */
    protected $managementUrl;
    /**
     * Do we use ServiceNet?
     *
     * @var boolean
     */
    protected $useServiceNet = false;
    /**
     * Constructor
     *
     * You must pass the account and the Rackspace authentication key.
     * Optional: the authentication url (default is US)
     *
     * @param string $user
     * @param string $key
     * @param string $authUrl
     */
    public function __construct($user, $key, $authUrl=self::US_AUTH_URL)
    {
        if (!isset($user)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception("The user cannot be empty");
        }
        if (!isset($key)) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception("The key cannot be empty");
        }
        if (!in_array($authUrl, array(self::US_AUTH_URL, self::UK_AUTH_URL))) {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception("The authentication URL should be valid");
        }
        $this->setUser($user);
        $this->setKey($key);
        $this->setAuthUrl($authUrl);
    }
    /**
     * Get User account
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * Get user key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
    /**
     * Get authentication URL
     *
     * @return string
     */
    public function getAuthUrl()
    {
        return $this->authUrl;
    }
    /**
     * Get the storage URL
     *
     * @return string|boolean
     */
    public function getStorageUrl()
    {
        if (empty($this->storageUrl)) {
            if (!$this->authenticate()) {
                return false;
            }
        }
        return $this->storageUrl;
    }
    /**
     * Get the CDN URL
     *
     * @return string|boolean
     */
    public function getCdnUrl()
    {
        if (empty($this->cdnUrl)) {
            if (!$this->authenticate()) {
                return false;
            }
        }
        return $this->cdnUrl;
    }
    /**
     * Get the management server URL
     *
     * @return string|boolean
     */
    public function getManagementUrl()
    {
        if (empty($this->managementUrl)) {
            if (!$this->authenticate()) {
                return false;
            }
        }
        return $this->managementUrl;
    }
    /**
     * Set the user account
     *
     * @param string $user
     * @return void
     */
    public function setUser($user)
    {
        if (!empty($user)) {
            $this->user = $user;
        }
    }
    /**
     * Set the authentication key
     *
     * @param string $key
     * @return void
     */
    public function setKey($key)
    {
        if (!empty($key)) {
            $this->key = $key;
        }
    }
    /**
     * Set the Authentication URL
     *
     * @param string $url
     * @return void
     */
    public function setAuthUrl($url)
    {
        if (!empty($url) && in_array($url, array(self::US_AUTH_URL, self::UK_AUTH_URL))) {
            $this->authUrl = $url;
        } else {
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception("The authentication URL is not valid");
        }
    }

    /**
     * Sets whether to use ServiceNet
     *
     * ServiceNet is Rackspace's internal network. Bandwidth on ServiceNet is
     * not charged.
     *
     * @param boolean $useServiceNet
     */
    public function setServiceNet($useServiceNet = true)
    {
        $this->useServiceNet = $useServiceNet;
        return $this;
    }

    /**
     * Get whether we're using ServiceNet
     *
     * @return boolean
     */
    public function getServiceNet()
    {
        return $this->useServiceNet;
    }

    /**
     * Get the authentication token
     *
     * @return string
     */
    public function getToken()
    {
        if (empty($this->token)) {
            if (!$this->authenticate()) {
                return false;
            }
        }
        return $this->token;
    }
    /**
     * Get the error msg of the last HTTP call
     *
     * @return string
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }
    /**
     * Get the error code of the last HTTP call
     *
     * @return strig
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
    /**
     * get the HttpClient instance
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        if (empty($this->httpClient)) {
            $this->httpClient = new Zend_Http_Client();
        }
        return $this->httpClient;
    }
    /**
     * Return true is the last call was successful
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return ($this->errorMsg=='');
    }
    /**
     * HTTP call
     *
     * @param string $url
     * @param string $method
     * @param array $headers
     * @param array $get
     * @param string $body
     * @return Zend_Http_Response
     */
    protected function httpCall($url,$method,$headers=array(),$data=array(),$body=null)
    {
        $client = $this->getHttpClient();
        $client->resetParameters(true);
        if ($method == 'PUT' && empty($body)) {
            // if left at NULL a PUT request will always have
            // Content-Type: x-url-form-encoded, which breaks copyObject()
            $client->setEncType('');
        }
        if (empty($headers[self::AUTHUSER_HEADER])) {
            $headers[self::AUTHTOKEN]= $this->getToken();
        }
        $client->setMethod($method);
        if (empty($data['format'])) {
            $data['format']= self::API_FORMAT;
        }
        $client->setParameterGet($data);
        if (!empty($body)) {
            $client->setRawData($body);
            if (!isset($headers['Content-Type'])) {
                $headers['Content-Type']= 'application/json';
            }
        }
        $client->setHeaders($headers);
        $client->setUri($url);
        $this->errorMsg='';
        $this->errorCode='';
        return $client->request();
    }
    /**
     * Authentication
     *
     * @return boolean
     */
    public function authenticate()
    {
        if (empty($this->user)) {
            /**
             * @see Zend_Service_Rackspace_Exception
             */
            #require_once 'Zend/Service/Rackspace/Exception.php';
            throw new Zend_Service_Rackspace_Exception("User has not been set");
        }

        $headers = array (
            self::AUTHUSER_HEADER => $this->user,
            self::AUTHKEY_HEADER => $this->key
        );
        $result = $this->httpCall($this->authUrl.'/'.self::VERSION,'GET', $headers);
        if ($result->getStatus()==204) {
            $this->token = $result->getHeader(self::AUTHTOKEN);
            $this->cdnUrl = $result->getHeader(self::CDNM_URL);
            $this->managementUrl = $result->getHeader(self::MANAGEMENT_URL);
            $storageUrl = $result->getHeader(self::STORAGE_URL);
            if ($this->useServiceNet) {
                $storageUrl = preg_replace('|(.*)://([^/]*)(.*)|', '$1://snet-$2$3', $storageUrl);
            }
            $this->storageUrl = $storageUrl;
            return true;
        }
        $this->errorMsg = $result->getBody();
        $this->errorCode = $result->getStatus();
        return false;
    }
}
