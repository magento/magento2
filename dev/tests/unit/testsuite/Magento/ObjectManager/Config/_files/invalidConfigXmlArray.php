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
    'preference_without_required_for_attribute' => array(
        '<?xml version="1.0"?><config><preference type="Some_Type_Name" /></config>',
        array("Element 'preference': The attribute 'for' is required but missing.")
    ),
    'preference_without_required_type_attribute' => array(
        '<?xml version="1.0"?><config><preference for="Some_For_Name" /></config>',
        array("Element 'preference': The attribute 'type' is required but missing.")
    ),
    'preferences_with_same_for_attribute_value' => array(
        '<?xml version="1.0"?>
        <config>
            <preference for="Some_For_Name" type="Some_Type_Name" />
            <preference for="Some_For_Name" type="Some_Type_Name" />
        </config>',
        array(
            "Element 'preference': Duplicate key-sequence ['Some_For_Name'] in unique " .
            "identity-constraint 'uniquePreference'."
        )
    ),
    'preferences_with_forbidden_attribute' => array(
        '<?xml version="1.0"?>
        <config><preference for="Some_For_Name" type="Some_Type_Name" forbidden="text"/></config>',
        array("Element 'preference', attribute 'forbidden': The attribute 'forbidden' is not allowed.")
    ),
    'type_without_required_name_attribute' => array(
        '<?xml version="1.0"?><config><type /></config>',
        array("Element 'type': The attribute 'name' is required but missing.")
    ),
    'type_with_same_name_attribute_value' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Type_name" />
            <type name="Some_Type_name" />
        </config>',
        array("Element 'type': Duplicate key-sequence ['Some_Type_name'] in unique identity-constraint 'uniqueType'.")
    ),
    'type_with_forbidden_attribute' => array(
        '<?xml version="1.0"?><config><type name="Some_Name" forbidden="text"/></config>',
        array("Element 'type', attribute 'forbidden': The attribute 'forbidden' is not allowed.")
    ),
    'type_shared_attribute_with_forbidden_value' => array(
        '<?xml version="1.0"?><config><type name="Some_Name" shared="forbidden"/></config>',
        array("Element 'type', attribute 'shared': 'forbidden' is not a valid value of the atomic type 'xs:boolean'.")
    ),
    'type_object_with_forbidden_shared_value' => array(
        '<?xml version="1.0"?>
        <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <type name="Some_Name">
                <arguments>
                    <argument name="Param_name" xsi:type="object" shared="forbidden">Object</argument>
                </arguments>
            </type>
        </config>',
        array(
            "Element 'argument', attribute 'shared': 'forbidden' is not a valid value of the atomic type 'xs:boolean'."
        )
    ),
    'type_instance_with_forbidden_attribute' => array(
        '<?xml version="1.0"?>
        <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <type name="Some_Name">
                <arguments>
                    <argument name="Param_name" xsi:type="object" forbidden="text">Object</argument>
                </arguments>
            </type>
        </config>',
        array("Element 'argument', attribute 'forbidden': The attribute 'forbidden' is not allowed.")
    ),
    'type_plugin_without_required_name_attribute' => array(
        '<?xml version="1.0"?><config><type name="Some_Name"><plugin /></type></config>',
        array("Element 'plugin': The attribute 'name' is required but missing.")
    ),
    'type_plugin_with_forbidden_attribute' => array(
        '<?xml version="1.0"?>
        <config><type name="Some_Name"><plugin name="some_name" forbidden="text" /></type></config>',
        array("Element 'plugin', attribute 'forbidden': The attribute 'forbidden' is not allowed.")
    ),
    'type_plugin_disabled_attribute_invalid_value' => array(
        '<?xml version="1.0"?>
        <config><type name="Some_Name"><plugin name="some_name" disabled="string" /></type></config>',
        array(
            "Element 'plugin', attribute 'disabled': 'string' is not a valid value of the atomic " .
            "type 'xs:boolean'."
        )
    ),
    'type_plugin_sortorder_attribute_invalid_value' => array(
        '<?xml version="1.0"?>
        <config><type name="Some_Name"><plugin name="some_name" sortOrder="string" /></type></config>',
        array("Element 'plugin', attribute 'sortOrder': 'string' is not a valid value of the atomic type 'xs:int'.")
    ),
    'type_with_same_argument_name_attribute' => array(
        '<?xml version="1.0"?>
        <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <type name="Some_Name">
                <arguments>
                    <argument name="same_argument_name" xsi:type="string">value</argument>
                    <argument name="same_argument_name" xsi:type="string">value2</argument>
                </arguments>
            </type>
        </config>',
        array(
            "Element 'argument': Duplicate key-sequence ['same_argument_name'] in key identity-constraint " .
            "'argumentName'."
        )
    ),
    'virtualtype_without_required_name_attribute' => array(
        '<?xml version="1.0"?><config><virtualType /></config>',
        array("Element 'virtualType': The attribute 'name' is required but missing.")
    ),
    'virtualtype_with_forbidden_shared_attribute_value' => array(
        '<?xml version="1.0"?><config><virtualType name="virtual_name" shared="forbidden"/></config>',
        array(
            "Element 'virtualType', attribute 'shared': 'forbidden' is not a valid value of the atomic " .
            "type 'xs:boolean'."
        )
    ),
    'virtualtype_with_forbidden_attribute' => array(
        '<?xml version="1.0"?><config><virtualType name="virtual_name" forbidden="text"/></config>',
        array("Element 'virtualType', attribute 'forbidden': The attribute 'forbidden' is not allowed.")
    ),
    'virtualtype_with_same_name_attribute_value' => array(
        '<?xml version="1.0"?><config><virtualType name="test_name" /><virtualType name="test_name" /></config>',
        array(
            "Element 'virtualType': Duplicate key-sequence ['test_name'] in unique" .
            " identity-constraint 'uniqueVirtualType'."
        )
    ),
    'virtualtype_with_same_argument_name_attribute' => array(
        '<?xml version="1.0"?>
        <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <virtualType name="virtual_name">
                <arguments>
                    <argument name="same_param_name" xsi:type="string">value</argument>
                    <argument name="same_param_name" xsi:type="string">value2</argument>
                </arguments>
            </virtualType>
        </config>',
        array(
            "Element 'argument': Duplicate key-sequence ['same_param_name'] in key identity-constraint 'argumentName'."
        )
    )
);
