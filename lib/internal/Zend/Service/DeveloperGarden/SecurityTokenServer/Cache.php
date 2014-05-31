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
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Cache.php 20166 2010-01-09 19:00:17Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage DeveloperGarden
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Marco Kaiser
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_DeveloperGarden_SecurityTokenServer_Cache
{
    /**
     * array with stored tokens
     *
     * @var array
     */
    protected static $_storedToken = array(
        'securityToken' => null,
        'getTokens' => null
    );

    /**
     * Internal cache for token values
     *
     * @var Zend_Cache_Core
     * @access private
     */
    private static $_cache = null;

    /**
     * PHP SOAP wsdl cache constant
     *
     * @var integer
     */
    private static $_wsdlCache = null;

// @codeCoverageIgnoreStart
    /**
     * Constructor overriding - make sure that a developer cannot instantiate
     */
    protected function __construct()
    {
    }
// @codeCoverageIgnoreEnd

    /**
     * returns stored token from cache or null
     *
     * @param string $tokenId
     * @throws Zend_Service_DeveloperGarden_Exception
     * @return Zend_Service_DeveloperGarden_Response_SecurityTokenServer_Interface|null
     */
    public static function getTokenFromCache($tokenId)
    {
        if (!array_key_exists($tokenId, self::$_storedToken)) {
            #require_once 'Zend/Service/DeveloperGarden/Exception.php';
            throw new Zend_Service_DeveloperGarden_Exception(
                'tokenID ' . $tokenId . ' unknown.'
            );
        }

        if (self::hasCache() && self::$_storedToken[$tokenId] === null) {
            $cache = self::getCache();
            $token = $cache->load(md5($tokenId));
            if ($token !== false) {
                self::$_storedToken[$tokenId] = $token;
            }
        }

        return self::$_storedToken[$tokenId];
    }

    /**
     * set new value for the given tokenId
     *
     * @param string $tokenId
     * @throws Zend_Service_DeveloperGarden_Exception
     * @param Zend_Service_DeveloperGarden_Response_SecurityTokenServer_Interface $tokenValue
     * @return void
     */
    public static function setTokenToCache($tokenId,
        Zend_Service_DeveloperGarden_Response_SecurityTokenServer_Interface $tokenValue
    ) {
        if (!array_key_exists($tokenId, self::$_storedToken)) {
            #require_once 'Zend/Service/DeveloperGarden/Exception.php';
            throw new Zend_Service_DeveloperGarden_Exception(
                'tokenID ' . $tokenId . ' unknown.'
            );
        }

        if (self::hasCache()) {
            $cache = self::getCache();
            $cache->save($tokenValue, md5($tokenId));
        }

        self::$_storedToken[$tokenId] = $tokenValue;
    }

    /**
     * reset the internal cache structure
     *
     * @return void
     */
    public static function resetTokenCache()
    {
        foreach (self::$_storedToken as $key => $value) {
            $value = null;
            self::$_storedToken[$key] = $value;
        }
    }

    /**
     * Returns the cache
     *
     * @return Zend_Cache_Core
     */
    public static function getCache()
    {
        return self::$_cache;
    }

    /**
     * Set a cache for token
     *
     * @param Zend_Cache_Core $cache A cache frontend
     */
    public static function setCache(Zend_Cache_Core $cache)
    {
        self::$_cache = $cache;
    }

    /**
     * Returns true when a cache is set
     *
     * @return boolean
     */
    public static function hasCache()
    {
        return (self::$_cache !== null);
    }

    /**
     * Removes any cache
     *
     * @return void
     */
    public static function removeCache()
    {
        self::$_cache = null;
    }

    /**
     * Clears all cache data
     *
     * @return void
     */
    public static function clearCache()
    {
        $cache = self::getCache();
        if (method_exists($cache, 'clean')) {
            $cache->clean();
        }
        self::$_wsdlCache = null;
    }

    /**
     * Returns the wsdl cache
     *
     * @return integer
     */
    public static function getWsdlCache()
    {
        return self::$_wsdlCache;
    }

    /**
     * Set a cache for wsdl file
     *
     * @param integer $cache
     * @return void
     */
    public static function setWsdlCache($cache = null)
    {
        self::$_wsdlCache = $cache;
    }
}
