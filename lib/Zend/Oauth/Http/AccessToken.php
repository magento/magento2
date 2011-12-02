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
 * @version    $Id: AccessToken.php 20217 2010-01-12 16:01:57Z matthew $
 */

/** Zend_Oauth_Http */
#require_once 'Zend/Oauth/Http.php';

/** Zend_Oauth_Token_Access */
#require_once 'Zend/Oauth/Token/Access.php';

/**
 * @category   Zend
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Oauth_Http_AccessToken extends Zend_Oauth_Http
{
    /**
     * Singleton instance if required of the HTTP client
     *
     * @var Zend_Http_Client
     */
    protected $_httpClient = null;

    /**
     * Initiate a HTTP request to retrieve an Access Token.
     *
     * @return Zend_Oauth_Token_Access
     */
    public function execute()
    {
        $params   = $this->assembleParams();
        $response = $this->startRequestCycle($params);
        $return   = new Zend_Oauth_Token_Access($response);
        return $return;
    }

    /**
     * Assemble all parameters for an OAuth Access Token request.
     *
     * @return array
     */
    public function assembleParams()
    {
        $params = array(
            'oauth_consumer_key'     => $this->_consumer->getConsumerKey(),
            'oauth_nonce'            => $this->_httpUtility->generateNonce(),
            'oauth_signature_method' => $this->_consumer->getSignatureMethod(),
            'oauth_timestamp'        => $this->_httpUtility->generateTimestamp(),
            'oauth_token'            => $this->_consumer->getLastRequestToken()->getToken(),
            'oauth_version'          => $this->_consumer->getVersion(),
        );

        if (!empty($this->_parameters)) {
            $params = array_merge($params, $this->_parameters);
        }

        $params['oauth_signature'] = $this->_httpUtility->sign(
            $params,
            $this->_consumer->getSignatureMethod(),
            $this->_consumer->getConsumerSecret(),
            $this->_consumer->getLastRequestToken()->getTokenSecret(),
            $this->_preferredRequestMethod,
            $this->_consumer->getAccessTokenUrl()
        );

        return $params;
    }

    /**
     * Generate and return a HTTP Client configured for the Header Request Scheme
     * specified by OAuth, for use in requesting an Access Token.
     *
     * @param  array $params
     * @return Zend_Http_Client
     */
    public function getRequestSchemeHeaderClient(array $params)
    {
        $params      = $this->_cleanParamsOfIllegalCustomParameters($params);
        $headerValue = $this->_toAuthorizationHeader($params);
        $client      = Zend_Oauth::getHttpClient();

        $client->setUri($this->_consumer->getAccessTokenUrl());
        $client->setHeaders('Authorization', $headerValue);
        $client->setMethod($this->_preferredRequestMethod);

        return $client;
    }

    /**
     * Generate and return a HTTP Client configured for the POST Body Request
     * Scheme specified by OAuth, for use in requesting an Access Token.
     *
     * @param  array $params
     * @return Zend_Http_Client
     */
    public function getRequestSchemePostBodyClient(array $params)
    {
        $params = $this->_cleanParamsOfIllegalCustomParameters($params);
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($this->_consumer->getAccessTokenUrl());
        $client->setMethod($this->_preferredRequestMethod);
        $client->setRawData(
            $this->_httpUtility->toEncodedQueryString($params)
        );
        $client->setHeaders(
            Zend_Http_Client::CONTENT_TYPE,
            Zend_Http_Client::ENC_URLENCODED
        );
        return $client;
    }

    /**
     * Generate and return a HTTP Client configured for the Query String Request
     * Scheme specified by OAuth, for use in requesting an Access Token.
     *
     * @param  array $params
     * @param  string $url
     * @return Zend_Http_Client
     */
    public function getRequestSchemeQueryStringClient(array $params, $url)
    {
        $params = $this->_cleanParamsOfIllegalCustomParameters($params);
        return parent::getRequestSchemeQueryStringClient($params, $url);
    }

    /**
     * Attempt a request based on the current configured OAuth Request Scheme and
     * return the resulting HTTP Response.
     *
     * @param  array $params
     * @return Zend_Http_Response
     */
    protected function _attemptRequest(array $params)
    {
        switch ($this->_preferredRequestScheme) {
            case Zend_Oauth::REQUEST_SCHEME_HEADER:
                $httpClient = $this->getRequestSchemeHeaderClient($params);
                break;
            case Zend_Oauth::REQUEST_SCHEME_POSTBODY:
                $httpClient = $this->getRequestSchemePostBodyClient($params);
                break;
            case Zend_Oauth::REQUEST_SCHEME_QUERYSTRING:
                $httpClient = $this->getRequestSchemeQueryStringClient($params,
                    $this->_consumer->getAccessTokenUrl());
                break;
        }
        return $httpClient->request();
    }

    /**
     * Access Token requests specifically may not contain non-OAuth parameters.
     * So these should be striped out and excluded. Detection is easy since
     * specified OAuth parameters start with "oauth_", Extension params start
     * with "xouth_", and no other parameters should use these prefixes.
     *
     * xouth params are not currently allowable.
     *
     * @param  array $params
     * @return array
     */
    protected function _cleanParamsOfIllegalCustomParameters(array $params)
    {
        foreach ($params as $key=>$value) {
            if (!preg_match("/^oauth_/", $key)) {
                unset($params[$key]);
            }
        }
        return $params;
    }
}
