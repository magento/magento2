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
 * @version    $Id: ClientLogin.php 20096 2010-01-06 02:05:09Z bkarwin $
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
 * Class to facilitate Google's "Account Authentication
 * for Installed Applications" also known as "ClientLogin".
 * @see http://code.google.com/apis/accounts/AuthForInstalledApps.html
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_ClientLogin
{

    /**
     * The Google client login URI
     *
     */
    const CLIENTLOGIN_URI = 'https://www.google.com/accounts/ClientLogin';

    /**
     * The default 'source' parameter to send to Google
     *
     */
    const DEFAULT_SOURCE = 'Zend-ZendFramework';

    /**
     * Set Google authentication credentials.
     * Must be done before trying to do any Google Data operations that
     * require authentication.
     * For example, viewing private data, or posting or deleting entries.
     *
     * @param string $email
     * @param string $password
     * @param string $service
     * @param Zend_Gdata_HttpClient $client
     * @param string $source
     * @param string $loginToken The token identifier as provided by the server.
     * @param string $loginCaptcha The user's response to the CAPTCHA challenge.
     * @param string $accountType An optional string to identify whether the
     * account to be authenticated is a google or a hosted account. Defaults to
     * 'HOSTED_OR_GOOGLE'. See: http://code.google.com/apis/accounts/docs/AuthForInstalledApps.html#Request
     * @throws Zend_Gdata_App_AuthException
     * @throws Zend_Gdata_App_HttpException
     * @throws Zend_Gdata_App_CaptchaRequiredException
     * @return Zend_Gdata_HttpClient
     */
    public static function getHttpClient($email, $password, $service = 'xapi',
        $client = null,
        $source = self::DEFAULT_SOURCE,
        $loginToken = null,
        $loginCaptcha = null,
        $loginUri = self::CLIENTLOGIN_URI,
        $accountType = 'HOSTED_OR_GOOGLE')
    {
        if (! ($email && $password)) {
            #require_once 'Zend/Gdata/App/AuthException.php';
            throw new Zend_Gdata_App_AuthException(
                   'Please set your Google credentials before trying to ' .
                   'authenticate');
        }

        if ($client == null) {
            $client = new Zend_Gdata_HttpClient();
        }
        if (!$client instanceof Zend_Http_Client) {
            #require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException(
                    'Client is not an instance of Zend_Http_Client.');
        }

        // Build the HTTP client for authentication
        $client->setUri($loginUri);
        $useragent = $source . ' Zend_Framework_Gdata/' . Zend_Version::VERSION;
        $client->setConfig(array(
                'maxredirects'    => 0,
                'strictredirects' => true,
                'useragent' => $useragent
            )
        );
        $client->setParameterPost('accountType', $accountType);
        $client->setParameterPost('Email', (string) $email);
        $client->setParameterPost('Passwd', (string) $password);
        $client->setParameterPost('service', (string) $service);
        $client->setParameterPost('source', (string) $source);
        if ($loginToken || $loginCaptcha) {
            if($loginToken && $loginCaptcha) {
                $client->setParameterPost('logintoken', (string) $loginToken);
                $client->setParameterPost('logincaptcha',
                        (string) $loginCaptcha);
            }
            else {
                #require_once 'Zend/Gdata/App/AuthException.php';
                throw new Zend_Gdata_App_AuthException(
                    'Please provide both a token ID and a user\'s response ' .
                    'to the CAPTCHA challenge.');
            }
        }

        // Send the authentication request
        // For some reason Google's server causes an SSL error. We use the
        // output buffer to supress an error from being shown. Ugly - but works!
        ob_start();
        try {
            $response = $client->request('POST');
        } catch (Zend_Http_Client_Exception $e) {
            #require_once 'Zend/Gdata/App/HttpException.php';
            throw new Zend_Gdata_App_HttpException($e->getMessage(), $e);
        }
        ob_end_clean();

        // Parse Google's response
        $goog_resp = array();
        foreach (explode("\n", $response->getBody()) as $l) {
            $l = chop($l);
            if ($l) {
                list($key, $val) = explode('=', chop($l), 2);
                $goog_resp[$key] = $val;
            }
        }

        if ($response->getStatus() == 200) {
            $client->setClientLoginToken($goog_resp['Auth']);
            $useragent = $source . ' Zend_Framework_Gdata/' . Zend_Version::VERSION;
            $client->setConfig(array(
                    'strictredirects' => true,
                    'useragent' => $useragent
                )
            );
            return $client;

        } elseif ($response->getStatus() == 403) {
            // Check if the server asked for a CAPTCHA
            if (array_key_exists('Error', $goog_resp) &&
                $goog_resp['Error'] == 'CaptchaRequired') {
                #require_once 'Zend/Gdata/App/CaptchaRequiredException.php';
                throw new Zend_Gdata_App_CaptchaRequiredException(
                    $goog_resp['CaptchaToken'], $goog_resp['CaptchaUrl']);
            }
            else {
                #require_once 'Zend/Gdata/App/AuthException.php';
                throw new Zend_Gdata_App_AuthException('Authentication with Google failed. Reason: ' .
                    (isset($goog_resp['Error']) ? $goog_resp['Error'] : 'Unspecified.'));
            }
        }
    }

}

