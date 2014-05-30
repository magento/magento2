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
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Config.php 22662 2010-07-24 17:37:36Z mabe $
 */

/** Zend_Oauth */
#require_once 'Zend/Oauth.php';

/** Zend_Uri */
#require_once 'Zend/Uri.php';

/** Zend_Oauth_Config_Interface */
#require_once 'Zend/Oauth/Config/ConfigInterface.php';

/**
 * @category   Zend
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Oauth_Config implements Zend_Oauth_Config_ConfigInterface
{
    /**
     * Signature method used when signing all parameters for an HTTP request
     *
     * @var string
     */
    protected $_signatureMethod = 'HMAC-SHA1';

    /**
     * Three request schemes are defined by OAuth, of which passing
     * all OAuth parameters by Header is preferred. The other two are
     * POST Body and Query String.
     *
     * @var string
     */
    protected $_requestScheme = Zend_Oauth::REQUEST_SCHEME_HEADER;

    /**
     * Preferred request Method - one of GET or POST - which Zend_Oauth
     * will enforce as standard throughout the library. Generally a default
     * of POST works fine unless a Provider specifically requires otherwise.
     *
     * @var string
     */
    protected $_requestMethod = Zend_Oauth::POST;

    /**
     * OAuth Version; This defaults to 1.0 - Must not be changed!
     *
     * @var string
     */
    protected $_version = '1.0';

    /**
     * This optional value is used to define where the user is redirected to
     * after authorizing a Request Token from an OAuth Providers website.
     * It's optional since a Provider may ask for this to be defined in advance
     * when registering a new application for a Consumer Key.
     *
     * @var string
     */
    protected $_callbackUrl = null;

    /**
     * The URL root to append default OAuth endpoint paths.
     *
     * @var string
     */
    protected $_siteUrl = null;

    /**
     * The URL to which requests for a Request Token should be directed.
     * When absent, assumed siteUrl+'/request_token'
     *
     * @var string
     */
    protected $_requestTokenUrl = null;

    /**
     * The URL to which requests for an Access Token should be directed.
     * When absent, assumed siteUrl+'/access_token'
     *
     * @var string
     */
    protected $_accessTokenUrl = null;

    /**
     * The URL to which users should be redirected to authorize a Request Token.
     * When absent, assumed siteUrl+'/authorize'
     *
     * @var string
     */
    protected $_authorizeUrl = null;

    /**
     * An OAuth application's Consumer Key.
     *
     * @var string
     */
    protected $_consumerKey = null;

    /**
     * Every Consumer Key has a Consumer Secret unless you're in RSA-land.
     *
     * @var string
     */
    protected $_consumerSecret = null;

    /**
     * If relevant, a PEM encoded RSA private key encapsulated as a
     * Zend_Crypt_Rsa Key
     *
     * @var Zend_Crypt_Rsa_Key_Private
     */
    protected $_rsaPrivateKey = null;

    /**
     * If relevant, a PEM encoded RSA public key encapsulated as a
     * Zend_Crypt_Rsa Key
     *
     * @var Zend_Crypt_Rsa_Key_Public
     */
    protected $_rsaPublicKey = null;

    /**
     * Generally this will nearly always be an Access Token represented as a
     * Zend_Oauth_Token_Access object.
     *
     * @var Zend_Oauth_Token
     */
    protected $_token = null;

    /**
     * Constructor; create a new object with an optional array|Zend_Config
     * instance containing initialising options.
     *
     * @param  array|Zend_Config $options
     * @return void
     */
    public function __construct($options = null)
    {
        if ($options !== null) {
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            }
            $this->setOptions($options);
        }
    }

    /**
     * Parse option array or Zend_Config instance and setup options using their
     * relevant mutators.
     *
     * @param  array|Zend_Config $options
     * @return Zend_Oauth_Config
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'consumerKey':
                    $this->setConsumerKey($value);
                    break;
                case 'consumerSecret':
                    $this->setConsumerSecret($value);
                    break;
                case 'signatureMethod':
                    $this->setSignatureMethod($value);
                    break;
                case 'version':
                    $this->setVersion($value);
                    break;
                case 'callbackUrl':
                    $this->setCallbackUrl($value);
                    break;
                case 'siteUrl':
                    $this->setSiteUrl($value);
                    break;
                case 'requestTokenUrl':
                    $this->setRequestTokenUrl($value);
                    break;
                case 'accessTokenUrl':
                    $this->setAccessTokenUrl($value);
                    break;
                case 'userAuthorizationUrl':
                    $this->setUserAuthorizationUrl($value);
                    break;
                case 'authorizeUrl':
                    $this->setAuthorizeUrl($value);
                    break;
                case 'requestMethod':
                    $this->setRequestMethod($value);
                    break;
                case 'rsaPrivateKey':
                    $this->setRsaPrivateKey($value);
                    break;
                case 'rsaPublicKey':
                    $this->setRsaPublicKey($value);
                    break;
            }
        }
        if (isset($options['requestScheme'])) {
            $this->setRequestScheme($options['requestScheme']);
        }

        return $this;
    }

    /**
     * Set consumer key
     *
     * @param  string $key
     * @return Zend_Oauth_Config
     */
    public function setConsumerKey($key)
    {
        $this->_consumerKey = $key;
        return $this;
    }

    /**
     * Get consumer key
     *
     * @return string
     */
    public function getConsumerKey()
    {
        return $this->_consumerKey;
    }

    /**
     * Set consumer secret
     *
     * @param  string $secret
     * @return Zend_Oauth_Config
     */
    public function setConsumerSecret($secret)
    {
        $this->_consumerSecret = $secret;
        return $this;
    }

    /**
     * Get consumer secret
     *
     * Returns RSA private key if set; otherwise, returns any previously set 
     * consumer secret.
     *
     * @return string
     */
    public function getConsumerSecret()
    {
        if ($this->_rsaPrivateKey !== null) {
            return $this->_rsaPrivateKey;
        }
        return $this->_consumerSecret;
    }

    /**
     * Set signature method
     *
     * @param  string $method
     * @return Zend_Oauth_Config
     * @throws Zend_Oauth_Exception if unsupported signature method specified
     */
    public function setSignatureMethod($method)
    {
        $method = strtoupper($method);
        if (!in_array($method, array(
                'HMAC-SHA1', 'HMAC-SHA256', 'RSA-SHA1', 'PLAINTEXT'
            ))
        ) {
            #require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception('Unsupported signature method: '
                . $method
                . '. Supported are HMAC-SHA1, RSA-SHA1, PLAINTEXT and HMAC-SHA256');
        }
        $this->_signatureMethod = $method;;
        return $this;
    }

    /**
     * Get signature method
     *
     * @return string
     */
    public function getSignatureMethod()
    {
        return $this->_signatureMethod;
    }

    /**
     * Set request scheme
     *
     * @param  string $scheme
     * @return Zend_Oauth_Config
     * @throws Zend_Oauth_Exception if invalid scheme specified, or if POSTBODY set when request method of GET is specified
     */
    public function setRequestScheme($scheme)
    {
        $scheme = strtolower($scheme);
        if (!in_array($scheme, array(
                Zend_Oauth::REQUEST_SCHEME_HEADER,
                Zend_Oauth::REQUEST_SCHEME_POSTBODY,
                Zend_Oauth::REQUEST_SCHEME_QUERYSTRING,
            ))
        ) {
            #require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $scheme . '\' is an unsupported request scheme'
            );
        }
        if ($scheme == Zend_Oauth::REQUEST_SCHEME_POSTBODY
            && $this->getRequestMethod() == Zend_Oauth::GET
        ) {
            #require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                'Cannot set POSTBODY request method if HTTP method set to GET'
            );
        }
        $this->_requestScheme = $scheme;
        return $this;
    }

    /**
     * Get request scheme
     *
     * @return string
     */
    public function getRequestScheme()
    {
        return $this->_requestScheme;
    }

    /**
     * Set version
     *
     * @param  string $version
     * @return Zend_Oauth_Config
     */
    public function setVersion($version)
    {
        $this->_version = $version;
        return $this;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Set callback URL
     *
     * @param  string $url
     * @return Zend_Oauth_Config
     * @throws Zend_Oauth_Exception for invalid URLs
     */
    public function setCallbackUrl($url)
    {
        if (!Zend_Uri::check($url)) {
            #require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $this->_callbackUrl = $url;
        return $this;
    }

    /**
     * Get callback URL
     *
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->_callbackUrl;
    }

    /**
     * Set site URL
     *
     * @param  string $url
     * @return Zend_Oauth_Config
     * @throws Zend_Oauth_Exception for invalid URLs
     */
    public function setSiteUrl($url)
    {
        if (!Zend_Uri::check($url)) {
            #require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $this->_siteUrl = $url;
        return $this;
    }

    /**
     * Get site URL
     *
     * @return string
     */
    public function getSiteUrl()
    {
        return $this->_siteUrl;
    }

    /**
     * Set request token URL
     *
     * @param  string $url
     * @return Zend_Oauth_Config
     * @throws Zend_Oauth_Exception for invalid URLs
     */
    public function setRequestTokenUrl($url)
    {
        if (!Zend_Uri::check($url)) {
            #require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $this->_requestTokenUrl = rtrim($url, '/');
        return $this;
    }

    /**
     * Get request token URL
     *
     * If no request token URL has been set, but a site URL has, returns the 
     * site URL with the string "/request_token" appended.
     *
     * @return string
     */
    public function getRequestTokenUrl()
    {
        if (!$this->_requestTokenUrl && $this->_siteUrl) {
            return $this->_siteUrl . '/request_token';
        }
        return $this->_requestTokenUrl;
    }

    /**
     * Set access token URL
     *
     * @param  string $url
     * @return Zend_Oauth_Config
     * @throws Zend_Oauth_Exception for invalid URLs
     */
    public function setAccessTokenUrl($url)
    {
        if (!Zend_Uri::check($url)) {
            #require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $this->_accessTokenUrl = rtrim($url, '/');
        return $this;
    }

    /**
     * Get access token URL
     *
     * If no access token URL has been set, but a site URL has, returns the 
     * site URL with the string "/access_token" appended.
     *
     * @return string
     */
    public function getAccessTokenUrl()
    {
        if (!$this->_accessTokenUrl && $this->_siteUrl) {
            return $this->_siteUrl . '/access_token';
        }
        return $this->_accessTokenUrl;
    }

    /**
     * Set user authorization URL
     *
     * @param  string $url
     * @return Zend_Oauth_Config
     * @throws Zend_Oauth_Exception for invalid URLs
     */
    public function setUserAuthorizationUrl($url)
    {
        return $this->setAuthorizeUrl($url);
    }

    /**
     * Set authorization URL
     *
     * @param  string $url
     * @return Zend_Oauth_Config
     * @throws Zend_Oauth_Exception for invalid URLs
     */
    public function setAuthorizeUrl($url)
    {
        if (!Zend_Uri::check($url)) {
            #require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $this->_authorizeUrl = rtrim($url, '/');
        return $this;
    }

    /**
     * Get user authorization URL
     *
     * @return string
     */
    public function getUserAuthorizationUrl()
    {
        return $this->getAuthorizeUrl();
    }

    /**
     * Get authorization URL
     *
     * If no authorization URL has been set, but a site URL has, returns the 
     * site URL with the string "/authorize" appended.
     *
     * @return string
     */
    public function getAuthorizeUrl()
    {
        if (!$this->_authorizeUrl && $this->_siteUrl) {
            return $this->_siteUrl . '/authorize';
        }
        return $this->_authorizeUrl;
    }

    /**
     * Set request method
     *
     * @param  string $method
     * @return Zend_Oauth_Config
     * @throws Zend_Oauth_Exception for invalid request methods
     */
    public function setRequestMethod($method)
    {
        $method = strtoupper($method);
        if (!in_array($method, array(
                Zend_Oauth::GET, 
                Zend_Oauth::POST, 
                Zend_Oauth::PUT, 
                Zend_Oauth::DELETE,
            ))
        ) {
            #require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception('Invalid method: ' . $method);
        }
        $this->_requestMethod = $method;
        return $this;
    }

    /**
     * Get request method
     *
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->_requestMethod;
    }

    /**
     * Set RSA public key
     *
     * @param  Zend_Crypt_Rsa_Key_Public $key
     * @return Zend_Oauth_Config
     */
    public function setRsaPublicKey(Zend_Crypt_Rsa_Key_Public $key)
    {
        $this->_rsaPublicKey = $key;
        return $this;
    }

    /**
     * Get RSA public key
     *
     * @return Zend_Crypt_Rsa_Key_Public
     */
    public function getRsaPublicKey()
    {
        return $this->_rsaPublicKey;
    }

    /**
     * Set RSA private key
     *
     * @param  Zend_Crypt_Rsa_Key_Private $key
     * @return Zend_Oauth_Config
     */
    public function setRsaPrivateKey(Zend_Crypt_Rsa_Key_Private $key)
    {
        $this->_rsaPrivateKey = $key;
        return $this;
    }

    /**
     * Get RSA private key
     *
     * @return Zend_Crypt_Rsa_Key_Private
     */
    public function getRsaPrivateKey()
    {
        return $this->_rsaPrivateKey;
    }

    /**
     * Set OAuth token
     *
     * @param  Zend_Oauth_Token $token
     * @return Zend_Oauth_Config
     */
    public function setToken(Zend_Oauth_Token $token)
    {
        $this->_token = $token;
        return $this;
    }

    /**
     * Get OAuth token
     *
     * @return Zend_Oauth_Token
     */
    public function getToken()
    {
        return $this->_token;
    }
}
