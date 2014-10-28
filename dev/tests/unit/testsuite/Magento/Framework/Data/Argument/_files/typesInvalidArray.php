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
return array(
    'no arguments' => array(
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" />',
        array("Element 'arguments': Missing child element(s). Expected is ( argument ).")
    ),
    'argument without type' => array(
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><argument/></arguments>',
        array("Element 'argument': The type definition is abstract.")
    ),
    'forbidden type used' => array(
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="forbiddenType">v</argument></arguments>',
        array(
            "Element 'argument', attribute '{http://www.w3.org/2001/XMLSchema-instance}type': The QName value " .
            "'forbiddenType' of the xsi:type attribute does not resolve to a type definition.",
            "Element 'argument': The type definition is abstract."
        )
    ),
    'abstract type argumentType used' => array(
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="argumentType">v</argument></arguments>',
        array("Element 'argument': The type definition is abstract.")
    ),
    'no name attribute' => array(
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument xsi:type="number">v</argument></arguments>',
        array("Element 'argument': The attribute 'name' is required but missing.")
    ),
    'forbidden attribute' => array(
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="string" forbiddenAttribute="w">v</argument></arguments>',
        array("Element 'argument', attribute 'forbiddenAttribute': The attribute 'forbiddenAttribute' is not allowed.")
    ),
    'forbidden translate attribute value for string' => array(
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="string" translate="forbidden">v</argument></arguments>',
        array(
            "Element 'argument', attribute 'translate': 'forbidden' is not a valid value of the atomic type " .
            "'xs:boolean'."
        )
    ),
    'attribute translate for non-string' => array(
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="boolean" translate="true">true</argument></arguments>',
        array("Element 'argument', attribute 'translate': The attribute 'translate' is not allowed.")
    ),
    'null type should be empty' => array(
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="null">v</argument></arguments>',
        array("Element 'argument': Character content is not allowed, because the content type is empty.")
    ),
    'forbidden child node' => array(
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="string"><child>v</child></argument></arguments>',
        array("Element 'child': This element is not expected.")
    ),
    'array with forbidden child' => array(
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="array"><child>v</child></argument></arguments>',
        array("Element 'child': This element is not expected. Expected is ( item ).")
    ),
    'array with 2 same items' => array(
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="array">
            <item name="name" xsi:type="string">v1</item>
            <item name="name" xsi:type="string">v2</item>
        </argument></arguments>',
        array("Element 'item': Duplicate key-sequence ['name'] in key identity-constraint 'argumentItemName'.")
    ),
    'array item without name' => array(
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="array"><item xsi:type="string">v</item></argument></arguments>',
        array(
            "Element 'item': The attribute 'name' is required but missing.",
            "Element 'item': Not all fields of key identity-constraint 'argumentItemName' evaluate to a node."
        )
    ),
    'array item with forbidden child' => array(
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="array">
            <item name="item" xsi:type="string"><child>v</child></item>
        </argument></arguments>',
        array("Element 'child': This element is not expected.")
    ),
    'nested array with same named items' => array(
        '<?xml version="1.0"?><arguments xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <argument name="a" xsi:type="array">
            <item name="item1" xsi:type="string">v</item>
            <item name="item2" xsi:type="array">
                <item name="item1" xsi:type="string">v</item>
            </item>
            <item name="item3" xsi:type="array">
                <item name="item4" xsi:type="string">v</item>
                <item name="item4" xsi:type="string">v</item>
            </item>
        </argument></arguments>',
        array("Element 'item': Duplicate key-sequence ['item4'] in key identity-constraint 'itemName'.")
    )
);
