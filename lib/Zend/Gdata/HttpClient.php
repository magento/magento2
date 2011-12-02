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
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: HttpClient.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Zend_Http_Client
 */
#require_once 'Zend/Http/Client.php';

/**
 * Gdata Http Client object.
 *
 * Class to extend the generic Zend Http Client with the ability to perform
 * secure AuthSub requests
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_HttpClient extends Zend_Http_Client
{

    /**
     * OpenSSL private key resource id
     * This key is used for AuthSub authentication.  If this value is set,
     * it is assuemd that secure AuthSub is desired.
     *
     * @var resource
     */
    private $_authSubPrivateKeyId = null;

    /**
     * Token for AuthSub authentication.
     * If this token is set, AuthSub authentication is used.
     *
     * @var string
     */
    private $_authSubToken = null;

    /**
     * Token for ClientLogin authentication.
     * If only this token is set, ClientLogin authentication is used.
     *
     * @var string
     */
    private $_clientLoginToken = null;

    /**
     * Token for ClientLogin authentication.
     * If this token is set, and the AuthSub key is not set,
     * ClientLogin authentication is used
     *
     * @var string
     */
    private $_clientLoginKey = null;

    /**
     * True if this request is being made with data supplied by
     * a stream object instead of a raw encoded string.
     *
     * @var bool
     */
    protected $_streamingRequest = null;

    /**
     * Sets the PEM formatted private key, as read from a file.
     *
     * This method reads the file and then calls setAuthSubPrivateKey()
     * with the file contents.
     *
     * @param string $file The location of the file containing the PEM key
     * @param string $passphrase The optional private key passphrase
     * @param bool $useIncludePath Whether to search the include_path
     *                             for the file
     * @return void
     */
    public function setAuthSubPrivateKeyFile($file, $passphrase = null,
                                             $useIncludePath = false) {
        $fp = @fopen($file, "r", $useIncludePath);
        if (!$fp) {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException('Failed to open private key file for AuthSub.');
        }

        $key = '';
        while (!feof($fp)) {
            $key .= fread($fp, 8192);
        }
        $this->setAuthSubPrivateKey($key, $passphrase);
        fclose($fp);
    }

    /**
     * Sets the PEM formatted private key to be used for secure AuthSub auth.
     *
     * In order to call this method, openssl must be enabled in your PHP
     * installation.  Otherwise, a Zend_Gdata_App_InvalidArgumentException
     * will be thrown.
     *
     * @param string $key The private key
     * @param string $passphrase The optional private key passphrase
     * @throws Zend_Gdata_App_InvalidArgumentException
     * @return Zend_Gdata_HttpClient Provides a fluent interface
     */
    public function setAuthSubPrivateKey($key, $passphrase = null) {
        if ($key != null && !function_exists('openssl_pkey_get_private')) {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'You cannot enable secure AuthSub if the openssl module ' .
                    'is not enabled in your PHP installation.');
        }
        $this->_authSubPrivateKeyId = openssl_pkey_get_private(
                $key, $passphrase);
        return $this;
    }

    /**
     * Gets the openssl private key id
     *
     * @return string The private key
     */
    public function getAuthSubPrivateKeyId() {
        return $this->_authSubPrivateKeyId;
    }

    /**
     * Gets the AuthSub token used for authentication
     *
     * @return string The token
     */
    public function getAuthSubToken() {
        return $this->_authSubToken;
    }

    /**
     * Sets the AuthSub token used for authentication
     *
     * @param string $token The token
     * @return Zend_Gdata_HttpClient Provides a fluent interface
     */
    public function setAuthSubToken($token) {
        $this->_authSubToken = $token;
        return $this;
    }

    /**
     * Gets the ClientLogin token used for authentication
     *
     * @return string The token
     */
    public function getClientLoginToken() {
        return $this->_clientLoginToken;
    }

    /**
     * Sets the ClientLogin token used for authentication
     *
     * @param string $token The token
     * @return Zend_Gdata_HttpClient Provides a fluent interface
     */
    public function setClientLoginToken($token) {
        $this->_clientLoginToken = $token;
        return $this;
    }

    /**
     * Filters the HTTP requests being sent to add the Authorization header.
     *
     * If both AuthSub and ClientLogin tokens are set,
     * AuthSub takes precedence.  If an AuthSub key is set, then
     * secure AuthSub authentication is used, and the request is signed.
     * Requests must be signed only with the private key corresponding to the
     * public key registered with Google.  If an AuthSub key is set, but
     * openssl support is not enabled in the PHP installation, an exception is
     * thrown.
     *
     * @param string $method The HTTP method
     * @param string $url The URL
     * @param array $headers An associate array of headers to be
     *                       sent with the request or null
     * @param string $body The body of the request or null
     * @param string $contentType The MIME content type of the body or null
     * @throws Zend_Gdata_App_Exception if there was a signing failure
     * @return array The processed values in an associative array,
     *               using the same names as the params
     */
    public function filterHttpRequest($method, $url, $headers = array(), $body = null, $contentType = null) {
        if ($this->getAuthSubToken() != null) {
            // AuthSub authentication
            if ($this->getAuthSubPrivateKeyId() != null) {
                // secure AuthSub
                $time = time();
                $nonce = mt_rand(0, 999999999);
                $dataToSign = $method . ' ' . $url . ' ' . $time . ' ' . $nonce;

                // compute signature
                $pKeyId = $this->getAuthSubPrivateKeyId();
                $signSuccess = openssl_sign($dataToSign, $signature, $pKeyId,
                                            OPENSSL_ALGO_SHA1);
                if (!$signSuccess) {
                    #require_once 'Zend/Gdata/App/Exception.php';
                    throw new Zend_Gdata_App_Exception(
                            'openssl_signing failure - returned false');
                }
                // encode signature
                $encodedSignature = base64_encode($signature);

                // final header
                $headers['authorization'] = 'AuthSub token="' . $this->getAuthSubToken() . '" ' .
                                            'data="' . $dataToSign . '" ' .
                                            'sig="' . $encodedSignature . '" ' .
                                            'sigalg="rsa-sha1"';
            } else {
                // AuthSub without secure tokens
                $headers['authorization'] = 'AuthSub token="' . $this->getAuthSubToken() . '"';
            }
        } elseif ($this->getClientLoginToken() != null) {
            $headers['authorization'] = 'GoogleLogin auth=' . $this->getClientLoginToken();
        }
        return array('method' => $method, 'url' => $url, 'body' => $body, 'headers' => $headers, 'contentType' => $contentType);
    }

    /**
     * Method for filtering the HTTP response, though no filtering is
     * currently done.
     *
     * @param Zend_Http_Response $response The response object to filter
     * @return Zend_Http_Response The filterd response object
     */
    public function filterHttpResponse($response) {
        return $response;
    }

    /**
     * Return the current connection adapter
     *
     * @return Zend_Http_Client_Adapter_Interface|string $adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

   /**
     * Load the connection adapter
     *
     * @param Zend_Http_Client_Adapter_Interface $adapter
     * @return void
     */
    public function setAdapter($adapter)
    {
        if ($adapter == null) {
            $this->adapter = $adapter;
        } else {
              parent::setAdapter($adapter);
        }
    }

    /**
     * Set the streamingRequest variable which controls whether we are
     * sending the raw (already encoded) POST data from a stream source.
     *
     * @param boolean $value The value to set.
     * @return void
     */
    public function setStreamingRequest($value)
    {
        $this->_streamingRequest = $value;
    }

    /**
     * Check whether the client is set to perform streaming requests.
     *
     * @return boolean True if yes, false otherwise.
     */
    public function getStreamingRequest()
    {
        if ($this->_streamingRequest()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Prepare the request body (for POST and PUT requests)
     *
     * @return string
     * @throws Zend_Http_Client_Exception
     */
    protected function _prepareBody()
    {
        if($this->_streamingRequest) {
            $this->setHeaders(self::CONTENT_LENGTH,
                $this->raw_post_data->getTotalSize());
            return $this->raw_post_data;
        }
        else {
            return parent::_prepareBody();
        }
    }

    /**
     * Clear all custom parameters we set.
     *
     * @return Zend_Http_Client
     */
    public function resetParameters($clearAll = false)
    {
        $this->_streamingRequest = false;

        return parent::resetParameters($clearAll);
    }

    /**
     * Set the raw (already encoded) POST data from a stream source.
     *
     * This is used to support POSTing from open file handles without
     * caching the entire body into memory. It is a wrapper around
     * Zend_Http_Client::setRawData().
     *
     * @param string $data The request data
     * @param string $enctype The encoding type
     * @return Zend_Http_Client
     */
    public function setRawDataStream($data, $enctype = null)
    {
        $this->_streamingRequest = true;
        return $this->setRawData($data, $enctype);
    }

}
