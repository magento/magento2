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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Service;

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
