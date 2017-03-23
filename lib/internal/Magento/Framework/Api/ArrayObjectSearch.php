<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Class to provide possibility to search for any object's property value by the name and value of another property
 */
class ArrayObjectSearch
{
    /**
     * Search for the value's value by specified key's name-value pair in the object
     * <pre>
     * Example of usage:
     * $data = array(
     *     ValidationRuleBuilderObject('name' => 'min_text_length', 'value' => 0)
     *     ValidationRuleBuilderObject('name' => 'max_text_length', 'value' => 255)
     *     ValidationRuleBuilderObject('anyOtherName' => 'customName', 'anyOtherValue' => 'customValue')
     * );
     *
     * Call:
     * $maxDateValue = ArrayObjectSearch::getArrayElementByName(
     *     $data,
     *     'max_text_length'
     * );
     * By default function looks for `value`'s value by the `name`'s value
     * Result: 255
     *
     * Call:
     * $customValue = ArrayObjectSearch::getArrayElementByName(
     *     $data,
     *     'customName',   //what key value to look for
     *     'anyOtherName', //where to look for
     *     'anyOtherValue' //where to return from
     * );
     * Result: 'customValue'
     * </pre>
     * @param object $data Object to search in
     * @param string $keyValue Value of the key property to search for
     * @param string $keyName Name of the key property to search for
     * @param string $valueName Name of the value property name
     * @return null|mixed
     */
    public static function getArrayElementByName($data, $keyValue, $keyName = 'name', $valueName = 'value')
    {
        $getter = 'get' . ucfirst($keyName);
        if (is_array($data)) {
            foreach ($data as $dataObject) {
                if (is_object($dataObject) && $dataObject->$getter() == $keyValue) {
                    $valueGetter = 'get' . ucfirst($valueName);
                    return $dataObject->$valueGetter();
                }
            }
        }
        return null;
    }
}
