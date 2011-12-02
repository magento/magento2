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
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Utils.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * Collection of utilities for various Zend_Service_Technorati classes.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Technorati_Utils
{
    /**
     * Parses, validates and returns a valid Zend_Uri object
     * from given $input.
     *
     * @param   string|Zend_Uri_Http $input
     * @return  null|Zend_Uri_Http
     * @throws  Zend_Service_Technorati_Exception
     * @static
     */
    public static function normalizeUriHttp($input)
    {
        // allow null as value
        if ($input === null) {
            return null;
        }

        /**
         * @see Zend_Uri
         */
        #require_once 'Zend/Uri.php';
        if ($input instanceof Zend_Uri_Http) {
            $uri = $input;
        } else {
            try {
                $uri = Zend_Uri::factory((string) $input);
            }
            // wrap exception under Zend_Service_Technorati_Exception object
            catch (Exception $e) {
                /**
                 * @see Zend_Service_Technorati_Exception
                 */
                #require_once 'Zend/Service/Technorati/Exception.php';
                throw new Zend_Service_Technorati_Exception($e->getMessage(), 0, $e);
            }
        }

        // allow inly Zend_Uri_Http objects or child classes
        if (!($uri instanceof Zend_Uri_Http)) {
            /**
             * @see Zend_Service_Technorati_Exception
             */
            #require_once 'Zend/Service/Technorati/Exception.php';
            throw new Zend_Service_Technorati_Exception(
                "Invalid URL $uri, only HTTP(S) protocols can be used");
        }

        return $uri;
    }
    /**
     * Parses, validates and returns a valid Zend_Date object
     * from given $input.
     *
     * $input can be either a string, an integer or a Zend_Date object.
     * If $input is string or int, it will be provided to Zend_Date as it is.
     * If $input is a Zend_Date object, the object instance will be returned.
     *
     * @param   mixed|Zend_Date $input
     * @return  null|Zend_Date
     * @throws  Zend_Service_Technorati_Exception
     * @static
     */
    public static function normalizeDate($input)
    {
        /**
         * @see Zend_Date
         */
        #require_once 'Zend/Date.php';
        /**
         * @see Zend_Locale
         */
        #require_once 'Zend/Locale.php';

        // allow null as value and return valid Zend_Date objects
        if (($input === null) || ($input instanceof Zend_Date)) {
            return $input;
        }

        // due to a BC break as of ZF 1.5 it's not safe to use Zend_Date::isDate() here
        // see ZF-2524, ZF-2334
        if (@strtotime($input) !== FALSE) {
            return new Zend_Date($input);
        } else {
            /**
             * @see Zend_Service_Technorati_Exception
             */
            #require_once 'Zend/Service/Technorati/Exception.php';
            throw new Zend_Service_Technorati_Exception("'$input' is not a valid Date/Time");
        }
    }

    /**
     * @todo public static function xpathQueryAndSet() {}
     */

    /**
     * @todo public static function xpathQueryAndSetIf() {}
     */

    /**
     * @todo public static function xpathQueryAndSetUnless() {}
     */
}
