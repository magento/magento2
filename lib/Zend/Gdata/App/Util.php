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
 * @subpackage App
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Util.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Utility class for static functions needed by Zend_Gdata_App
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage App
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_App_Util
{

    /**
     *  Convert timestamp into RFC 3339 date string.
     *  2005-04-19T15:30:00
     *
     * @param int $timestamp
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public static function formatTimestamp($timestamp)
    {
        $rfc3339 = '/^(\d{4})\-?(\d{2})\-?(\d{2})((T|t)(\d{2})\:?(\d{2})' .
                   '\:?(\d{2})(\.\d{1,})?((Z|z)|([\+\-])(\d{2})\:?(\d{2})))?$/';

        if (ctype_digit($timestamp)) {
            return gmdate('Y-m-d\TH:i:sP', $timestamp);
        } elseif (preg_match($rfc3339, $timestamp) > 0) {
            // timestamp is already properly formatted
            return $timestamp;
        } else {
            $ts = strtotime($timestamp);
            if ($ts === false) {
                #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
                throw new Zend_Gdata_App_InvalidArgumentException("Invalid timestamp: $timestamp.");
            }
            return date('Y-m-d\TH:i:s', $ts);
        }
    }

    /** Find the greatest key that is less than or equal to a given upper
      * bound, and return the value associated with that key.
      *
      * @param integer|null $maximumKey The upper bound for keys. If null, the
      *        maxiumum valued key will be found.
      * @param array $collection An two-dimensional array of key/value pairs
      *        to search through.
      * @returns mixed The value corresponding to the located key.
      * @throws Zend_Gdata_App_Exception Thrown if $collection is empty.
      */
    public static function findGreatestBoundedValue($maximumKey, $collection)
    {
        $found = false;
        $foundKey = $maximumKey;

        // Sanity check: Make sure that the collection isn't empty
        if (sizeof($collection) == 0) {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception("Empty namespace collection encountered.");
        }

        if ($maximumKey === null) {
            // If the key is null, then we return the maximum available
            $keys = array_keys($collection);
            sort($keys);
            $found = true;
            $foundKey = end($keys);
        } else {
            // Otherwise, we optimistically guess that the current version
            // will have a matching namespce. If that fails, we decrement the
            // version until we find a match.
            while (!$found && $foundKey >= 0) {
                if (array_key_exists($foundKey, $collection))
                    $found = true;
                else
                    $foundKey--;
            }
        }

        // Guard: A namespace wasn't found. Either none were registered, or
        // the current protcol version is lower than the maximum namespace.
        if (!$found) {
            #require_once 'Zend/Gdata/App/Exception.php';
            throw new Zend_Gdata_App_Exception("Namespace compatible with current protocol not found.");
        }

        return $foundKey;
    }

}
