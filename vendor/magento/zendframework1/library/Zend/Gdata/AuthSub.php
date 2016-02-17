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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Zend_Gdata_HttpClient
 */
#require_once 'Zend/Gdata/HttpClient.php';

/**
 * Zend_Version
 */
#require_once 'Zend/Version.php';

/**
 * Wrapper around Zend_Http_Client to facilitate Google's "Account Authentication
 * Proxy for Web-Based Applications".
 *
 * @see http://code.google.com/apis/accounts/AuthForWebApps.html
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_AuthSub
{

    const AUTHSUB_REQUEST_URI      = 'https://www.google.com/accounts/AuthSubRequest';

    const AUTHSUB_SESSION_TOKEN_URI = 'https://www.google.com/accounts/AuthSubSessionToken';

    const AUTHSUB_REVOKE_TOKEN_URI  = 'https://www.google.com/accounts/AuthSubRevokeToken';

    const AUTHSUB_TOKEN_INFO_URI    = 'https://www.google.com/accounts/AuthSubTokenInfo';

     /**
      * Creates a URI to request a single-use AuthSub token.
      *
      * @param string $next (required) URL identifying the service to be
      *                     accessed.
      *  The resulting token will enable access to the specified service only.
      *  Some services may limit scope further, such as read-only access.
      * @param string $scope (required) URL identifying the service to be
      *                      accessed.  The resulting token will enable
      *                      access to the specified service only.
      *                      Some services may limit scope further, such
      *                      as read-only access.
      * @param int $secure (optional) Boolean flag indicating whether the
      *                    authentication transaction should issue a secure
      *                    token (1) or a non-secure token (0). Secure tokens
      *                    are available to registered applications only.
      * @param int $session (optional) Boolean flag indicating whether
      *                     the one-time-use  token may be exchanged for
      *                     a session token (1) or not (0).
      * @param string $request_uri (optional) URI to which to direct the
      *                            authentication request.
      */
     public static function getAuthSubTokenUri($next, $scope, $secure=0, $session=0,
                                               $request_uri = self::AUTHSUB_REQUEST_URI)
     {
         $querystring = '?next=' . urlencode($next)
             . '&scope=' . urldecode($scope)
             . '&secure=' . urlencode($secure)
             . '&session=' . urlencode($session);
         return $request_uri . $querystring;
     }


    /**
     * Upgrades a single use token to a session token
     *
     * @param string $token The single use token which is to be upgraded
     * @param Zend_Http_Client $client (optional) HTTP client to use to
     *                                 make the request
     * @param string $request_uri (optional) URI to which to direct
     *                            the session token upgrade
     * @return string The upgraded token value
     * @throws Zend_Gdata_App_AuthException
     * @throws Zend_Gdata_App_HttpException
     */
    public static function getAuthSubSessionToken(
            $token, $client = null,
            $request_uri = self::AUTHSUB_SESSION_TOKEN_URI)
    {
        $client = self::getHttpClient($token, $client);

        if ($client instanceof Zend_Gdata_HttpClient) {
            $filterResult = $client->filterHttpRequest('GET', $request_uri);
            $url = $filterResult['url'];
            $headers = $filterResult['headers'];
            $client->setHeaders($headers);
            $client->setUri($url);
        } else {
            $client->setUri($request_uri);
        }

        try {
            $response = $client->request('GET');
        } catch (Zend_Http_Client_Exception $e) {
            #require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException($e->getMessage(), $e);
        }

        // Parse Google's response
        if ($response->isSuccessful()) {
            $goog_resp = array();
            foreach (explode("\n", $response->getBody()) as $l) {
                $l = chop($l);
                if ($l) {
                    list($key, $val) = explode('=', chop($l), 2);
                    $goog_resp[$key] = $val;
                }
            }
            return $goog_resp['Token'];
        } else {
            #require_once 'Zend/Gdata/App/AuthException.php';
            throw new Zend_Gdata_App_AuthException(
                    'Token upgrade failed. Reason: ' . $response->getBody());
        }
    }

    /**
     * Revoke a token
     *
     * @param string $token The token to revoke
     * @param Zend_Http_Client $client (optional) HTTP client to use to make the request
     * @param string $request_uri (optional) URI to which to direct the revokation request
     * @return boolean Whether the revokation was successful
     * @throws Zend_Gdata_App_HttpException
     */
    public static function AuthSubRevokeToken($token, $client = null,
                                              $request_uri = self::AUTHSUB_REVOKE_TOKEN_URI)
    {
        $client = self::getHttpClient($token, $client);

        if ($client instanceof Zend_Gdata_HttpClient) {
            $filterResult = $client->filterHttpRequest('GET', $request_uri);
            $url = $filterResult['url'];
            $headers = $filterResult['headers'];
            $client->setHeaders($headers);
            $client->setUri($url);
            $client->resetParameters();
        } else {
            $client->setUri($request_uri);
        }

        ob_start();
        try {
            $response = $client->request('GET');
        } catch (Zend_Http_Client_Exception $e) {
            ob_end_clean();
            #require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException($e->getMessage(), $e);
        }
        ob_end_clean();
        // Parse Google's response
        if ($response->isSuccessful()) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * get token information
     *
     * @param string $token The token to retrieve information about
     * @param Zend_Http_Client $client (optional) HTTP client to use to
     *                                 make the request
     * @param string $request_uri (optional) URI to which to direct
     *                            the information request
     */
    public static function getAuthSubTokenInfo(
            $token, $client = null, $request_uri = self::AUTHSUB_TOKEN_INFO_URI)
    {
        $client = self::getHttpClient($token, $client);

        if ($client instanceof Zend_Gdata_HttpClient) {
            $filterResult = $client->filterHttpRequest('GET', $request_uri);
            $url = $filterResult['url'];
            $headers = $filterResult['headers'];
            $client->setHeaders($headers);
            $client->setUri($url);
        } else {
            $client->setUri($request_uri);
        }

        ob_start();
        try {
            $response = $client->request('GET');
        } catch (Zend_Http_Client_Exception $e) {
            ob_end_clean();
            #require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException($e->getMessage(), $e);
        }
        ob_end_clean();
        return $response->getBody();
    }

    /**
     * Retrieve a HTTP client object with AuthSub credentials attached
     * as the Authorization header
     *
     * @param string $token The token to retrieve information about
     * @param Zend_Gdata_HttpClient $client (optional) HTTP client to use to make the request
     */
    public static function getHttpClient($token, $client = null)
    {
        if ($client == null) {
            $client = new Zend_Gdata_HttpClient();
        }
        if (!$client instanceof Zend_Gdata_HttpClient) {
            #require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException('Client is not an instance of Zend_Gdata_HttpClient.');
        }
        $useragent = 'Zend_Framework_Gdata/' . Zend_Version::VERSION;
        $client->setConfig(array(
                'strictredirects' => true,
                'useragent' => $useragent
            )
        );
        $client->setAuthSubToken($token);
        return $client;
    }

}
