<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Utility class for mapping data between objects or arrays
 */
namespace Magento\Framework\Object;

class Mapper
{
    /**
     * Convert data from source to target item using map array
     *
     * Will get or set data with generic or magic, or specified Magento Object methods, or with array keys
     * from or to \Magento\Framework\Object or array
     * :)
     *
     * Map must either be associative array of keys from=>to
     * or a numeric array of keys, assuming from = to
     *
     * Defaults must be assoc array of keys => values. Target will get default, if the value is not present in source
     * If the source has getter defined instead of magic method, the value will be taken only if not empty
     *
     * Callbacks explanation (when $from or $to is not array):
     *   for $from:
     *     <\Magento\Framework\Object> => $from->getData($key) (default)
     *     array(<\Magento\Framework\Object>, <method>) => $from->$method($key)
     *   for $to (makes sense only for \Magento\Framework\Object):
     *     <\Magento\Framework\Object> => $from->setData($key, <from>)
     *     array(<\Magento\Framework\Object>, <method>) => $from->$method($key, <from>)
     *
     * @param array|\Magento\Framework\Object|callback $from
     * @param array|\Magento\Framework\Object|callback $to
     * @param array $map
     * @param array $defaults
     * @return array|object
     */
    public static function &accumulateByMap($from, $to, array $map, array $defaults = array())
    {
        $get = 'getData';
        if (is_array(
            $from
        ) && isset(
            $from[0]
        ) && is_object(
            $from[0]
        ) && isset(
            $from[1]
        ) && is_string(
            $from[1]
        ) && is_callable(
            $from
        )
        ) {
            list($from, $get) = $from;
        }
        $fromIsArray = is_array($from);
        $fromIsVO = $from instanceof \Magento\Framework\Object;

        $set = 'setData';
        if (is_array(
            $to
        ) && isset(
            $to[0]
        ) && is_object(
            $to[0]
        ) && isset(
            $to[1]
        ) && is_string(
            $to[1]
        ) && is_callable(
            $to
        )
        ) {
            list($to, $set) = $to;
        }
        $toIsArray = is_array($to);
        $toIsVO = $to instanceof \Magento\Framework\Object;

        foreach ($map as $keyFrom => $keyTo) {
            if (!is_string($keyFrom)) {
                $keyFrom = $keyTo;
            }
            if ($fromIsArray) {
                if (array_key_exists($keyFrom, $from)) {
                    if ($toIsArray) {
                        $to[$keyTo] = $from[$keyFrom];
                    } elseif ($toIsVO) {
                        $to->{$set}($keyTo, $from[$keyFrom]);
                    }
                }
            } elseif ($fromIsVO) {
                // get value if (any) value is found as in magic data or a non-empty value with declared getter
                $value = null;
                if ($shouldGet = $from->hasData($keyFrom)) {
                    $value = $from->{$get}($keyFrom);
                } elseif (method_exists($from, $get)) {
                    $value = $from->{$get}($keyFrom);
                    if ($value) {
                        $shouldGet = true;
                    }
                }
                if ($shouldGet) {
                    if ($toIsArray) {
                        $to[$keyTo] = $value;
                    } elseif ($toIsVO) {
                        $to->{$set}($keyTo, $value);
                    }
                }
            }
        }
        foreach ($defaults as $keyTo => $value) {
            if ($toIsArray) {
                if (!isset($to[$keyTo])) {
                    $to[$keyTo] = $value;
                }
            } elseif ($toIsVO) {
                if (!$to->hasData($keyTo)) {
                    $to->{$set}($keyTo, $value);
                }
            }
        }
        return $to;
    }
}
