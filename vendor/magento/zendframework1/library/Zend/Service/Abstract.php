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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * Zend_Http_Client
 */
#require_once 'Zend/Http/Client.php';


/**
 * @category   Zend
 * @package    Zend_Service
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Service_Abstract
{
    /**
     * HTTP Client used to query all web services
     *
     * @var Zend_Http_Client
     */
    protected static $_httpClient = null;


    /**
     * Sets the HTTP client object to use for retrieving the feeds.  If none
     * is set, the default Zend_Http_Client will be used.
     *
     * @param Zend_Http_Client $httpClient
     */
    final public static function setHttpClient(Zend_Http_Client $httpClient)
    {
        self::$_httpClient = $httpClient;
    }


    /**
     * Gets the HTTP client object.
     *
     * @return Zend_Http_Client
     */
    final public static function getHttpClient()
    {
        if (!self::$_httpClient instanceof Zend_Http_Client) {
            self::$_httpClient = new Zend_Http_Client();
        }

        return self::$_httpClient;
    }
}

