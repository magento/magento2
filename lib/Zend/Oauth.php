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
 * @version    $Id: Oauth.php 21070 2010-02-16 14:34:25Z padraic $
 */

/** Zend_Http_Client */
#require_once 'Zend/Http/Client.php';

/**
 * @category   Zend
 * @package    Zend_Oauth
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Oauth
{
    const REQUEST_SCHEME_HEADER      = 'header';
    const REQUEST_SCHEME_POSTBODY    = 'postbody';
    const REQUEST_SCHEME_QUERYSTRING = 'querystring';
    const GET                        = 'GET';
    const POST                       = 'POST';
    const PUT                        = 'PUT';
    const DELETE                     = 'DELETE';
    const HEAD                       = 'HEAD';

    /**
     * Singleton instance if required of the HTTP client
     *
     * @var Zend_Http_Client
     */
    protected static $httpClient = null;

    /**
     * Allows the external environment to make Zend_Oauth use a specific
     * Client instance.
     *
     * @param Zend_Http_Client $httpClient
     * @return void
     */
    public static function setHttpClient(Zend_Http_Client $httpClient)
    {
        self::$httpClient = $httpClient;
    }

    /**
     * Return the singleton instance of the HTTP Client. Note that
     * the instance is reset and cleared of previous parameters and
     * Authorization header values.
     *
     * @return Zend_Http_Client
     */
    public static function getHttpClient()
    {
        if (!isset(self::$httpClient)) {
            self::$httpClient = new Zend_Http_Client;
        } else {
            self::$httpClient->setHeaders('Authorization', null);
            self::$httpClient->resetParameters();
        }
        return self::$httpClient;
    }

    /**
     * Simple mechanism to delete the entire singleton HTTP Client instance
     * which forces an new instantiation for subsequent requests.
     *
     * @return void
     */
    public static function clearHttpClient()
    {
        self::$httpClient = null;
    }
}
