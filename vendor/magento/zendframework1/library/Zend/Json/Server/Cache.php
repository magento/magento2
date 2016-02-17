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
 * @package    Zend_Json
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Server_Cache */
#require_once 'Zend/Server/Cache.php';

/**
 * Zend_Json_Server_Cache: cache Zend_Json_Server server definition and SMD
 *
 * @category   Zend
 * @package    Zend_Json
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Json_Server_Cache extends Zend_Server_Cache
{
    /**
     * Cache a service map description (SMD) to a file
     *
     * Returns true on success, false on failure
     *
     * @param  string $filename
     * @param  Zend_Json_Server $server
     * @return boolean
     */
    public static function saveSmd($filename, Zend_Json_Server $server)
    {
        if (!is_string($filename)
            || (!file_exists($filename) && !is_writable(dirname($filename))))
        {
            return false;
        }

        if (0 === @file_put_contents($filename, $server->getServiceMap()->toJson())) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve a cached SMD
     *
     * On success, returns the cached SMD (a JSON string); an failure, returns
     * boolean false.
     *
     * @param  string $filename
     * @return string|false
     */
    public static function getSmd($filename)
    {
        if (!is_string($filename)
            || !file_exists($filename)
            || !is_readable($filename))
        {
            return false;
        }


        if (false === ($smd = @file_get_contents($filename))) {
            return false;
        }

        return $smd;
    }

    /**
     * Delete a file containing a cached SMD
     *
     * @param  string $filename
     * @return bool
     */
    public static function deleteSmd($filename)
    {
        if (is_string($filename) && file_exists($filename)) {
            unlink($filename);
            return true;
        }

        return false;
    }
}
