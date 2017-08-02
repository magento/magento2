<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Utility class for mapping data between objects or arrays
 */
namespace Magento\Framework\DataObject;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @since 2.0.0
 */
class Mapper
{
    /**
     * Convert data from source to target item using map array
     *
     * Will get or set data with generic or magic, or specified Magento Object methods, or with array keys
     * from or to \Magento\Framework\DataObject or array
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
     *     <\Magento\Framework\DataObject> => $from->getData($key) (default)
     *     array(<\Magento\Framework\DataObject>, <method>) => $from->$method($key)
     *   for $to (makes sense only for \Magento\Framework\DataObject):
     *     <\Magento\Framework\DataObject> => $from->setData($key, <from>)
     *     array(<\Magento\Framework\DataObject>, <method>) => $from->$method($key, <from>)
     *
     * @param array|\Magento\Framework\DataObject|callback $from
     * @param array|\Magento\Framework\DataObject|callback $to
     * @param array $map
     * @param array $defaults
     * @return array|object
     * @since 2.0.0
     */
    public static function &accumulateByMap($from, $to, array $map, array $defaults = [])
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
        $fromIsVO = $from instanceof \Magento\Framework\DataObject;

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
        $toIsVO = $to instanceof \Magento\Framework\DataObject;

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
