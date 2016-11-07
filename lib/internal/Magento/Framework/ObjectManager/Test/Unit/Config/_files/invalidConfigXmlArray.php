<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'preference_without_required_for_attribute' => [
        '<?xml version="1.0"?><config><preference type="Some_Type_Name" /></config>',
        ["Element 'preference': The attribute 'for' is required but missing.\nLine: 1\n"],
    ],
    'preference_without_required_type_attribute' => [
        '<?xml version="1.0"?><config><preference for="Some_For_Name" /></config>',
        ["Element 'preference': The attribute 'type' is required but missing.\nLine: 1\n"],
    ],
    'preferences_with_same_for_attribute_value' => [
        '<?xml version="1.0"?>
        <config>
            <preference for="Some_For_Name" type="Some_Type_Name" />
            <preference for="Some_For_Name" type="Some_Type_Name" />
        </config>',
        [
            "Element 'preference': Duplicate key-sequence ['Some_For_Name'] in unique " .
            "identity-constraint 'uniquePreference'.\nLine: 4\n"
        ],
    ],
    'preferences_with_forbidden_attribute' => [
        '<?xml version="1.0"?>
        <config><preference for="Some_For_Name" type="Some_Type_Name" forbidden="text"/></config>',
        ["Element 'preference', attribute 'forbidden': The attribute 'forbidden' is not allowed.\nLine: 2\n"],
    ],
    'type_without_required_name_attribute' => [
        '<?xml version="1.0"?><config><type /></config>',
        ["Element 'type': The attribute 'name' is required but missing.\nLine: 1\n"],
    ],
    'type_with_same_name_attribute_value' => [
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Type_name" />
            <type name="Some_Type_name" />
        </config>',
        [
            "Element 'type': Duplicate key-sequence ['Some_Type_name'] in unique identity-constraint"
                . " 'uniqueType'.\nLine: 4\n"
        ],
    ],
    'type_with_forbidden_attribute' => [
        '<?xml version="1.0"?><config><type name="Some_Name" forbidden="text"/></config>',
        ["Element 'type', attribute 'forbidden': The attribute 'forbidden' is not allowed.\nLine: 1\n"],
    ],
    'type_shared_attribute_with_forbidden_value' => [
        '<?xml version="1.0"?><config><type name="Some_Name" shared="forbidden"/></config>',
        [
            "Element 'type', attribute 'shared': 'forbidden' is not a valid value of the atomic type"
                . " 'xs:boolean'.\nLine: 1\n"
        ],
    ],
    'type_object_with_forbidden_shared_value' => [
        '<?xml version="1.0"?>
        <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <type name="Some_Name">
                <arguments>
                    <argument name="Param_name" xsi:type="object" shared="forbidden">Object</argument>
                </arguments>
            </type>
        </config>',
        [
            "Element 'argument', attribute 'shared': 'forbidden' is not a valid value of the atomic type"
                . " 'xs:boolean'.\nLine: 5\n"
        ],
    ],
    'type_instance_with_forbidden_attribute' => [
        '<?xml version="1.0"?>
        <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <type name="Some_Name">
                <arguments>
                    <argument name="Param_name" xsi:type="object" forbidden="text">Object</argument>
                </arguments>
            </type>
        </config>',
        ["Element 'argument', attribute 'forbidden': The attribute 'forbidden' is not allowed.\nLine: 5\n"],
    ],
    'type_plugin_without_required_name_attribute' => [
        '<?xml version="1.0"?><config><type name="Some_Name"><plugin /></type></config>',
        ["Element 'plugin': The attribute 'name' is required but missing.\nLine: 1\n"],
    ],
    'type_plugin_with_forbidden_attribute' => [
        '<?xml version="1.0"?>
        <config><type name="Some_Name"><plugin name="some_name" forbidden="text" /></type></config>',
        ["Element 'plugin', attribute 'forbidden': The attribute 'forbidden' is not allowed.\nLine: 2\n"],
    ],
    'type_plugin_disabled_attribute_invalid_value' => [
        '<?xml version="1.0"?>
        <config><type name="Some_Name"><plugin name="some_name" disabled="string" /></type></config>',
        [
            "Element 'plugin', attribute 'disabled': 'string' is not a valid value of the atomic " .
            "type 'xs:boolean'.\nLine: 2\n"
        ],
    ],
    'type_plugin_sortorder_attribute_invalid_value' => [
        '<?xml version="1.0"?>
        <config><type name="Some_Name"><plugin name="some_name" sortOrder="string" /></type></config>',
        [
            "Element 'plugin', attribute 'sortOrder': 'string' is not a valid value of the atomic type"
                . " 'xs:int'.\nLine: 2\n"
        ],
    ],
    'type_with_same_argument_name_attribute' => [
        '<?xml version="1.0"?>
        <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <type name="Some_Name">
                <arguments>
                    <argument name="same_argument_name" xsi:type="string">value</argument>
                    <argument name="same_argument_name" xsi:type="string">value2</argument>
                </arguments>
            </type>
        </config>',
        [
            "Element 'argument': Duplicate key-sequence ['same_argument_name'] in key identity-constraint " .
            "'argumentName'.\nLine: 6\n"
        ],
    ],
    'virtualtype_without_required_name_attribute' => [
        '<?xml version="1.0"?><config><virtualType /></config>',
        ["Element 'virtualType': The attribute 'name' is required but missing.\nLine: 1\n"],
    ],
    'virtualtype_with_forbidden_shared_attribute_value' => [
        '<?xml version="1.0"?><config><virtualType name="virtual_name" shared="forbidden"/></config>',
        [
            "Element 'virtualType', attribute 'shared': 'forbidden' is not a valid value of the atomic " .
            "type 'xs:boolean'.\nLine: 1\n"
        ],
    ],
    'virtualtype_with_forbidden_attribute' => [
        '<?xml version="1.0"?><config><virtualType name="virtual_name" forbidden="text"/></config>',
        ["Element 'virtualType', attribute 'forbidden': The attribute 'forbidden' is not allowed.\nLine: 1\n"],
    ],
    'virtualtype_with_same_name_attribute_value' => [
        '<?xml version="1.0"?><config><virtualType name="test_name" /><virtualType name="test_name" /></config>',
        [
            "Element 'virtualType': Duplicate key-sequence ['test_name'] in unique" .
            " identity-constraint 'uniqueVirtualType'.\nLine: 1\n"
        ],
    ],
    'virtualtype_with_same_argument_name_attribute' => [
        '<?xml version="1.0"?>
        <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <virtualType name="virtual_name">
                <arguments>
                    <argument name="same_param_name" xsi:type="string">value</argument>
                    <argument name="same_param_name" xsi:type="string">value2</argument>
                </arguments>
            </virtualType>
        </config>',
        [
            "Element 'argument': Duplicate key-sequence ['same_param_name'] in key identity-constraint"
                . " 'argumentName'.\nLine: 6\n"
        ],
    ],
    'sorted_object_list_with_invalid_sortOrder_attribute_value' => [
        '<?xml version="1.0"?>
        <config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <type name="Some_Name">
                <arguments>
                    <argument name="sorted_object_list" xsi:type="array">
                        <item name="someObject" xsi:type="object" sortOrder="false">Some_Class_Name</item>
                    </argument>
                </arguments>
            </type>
        </config>',
        [
            "Element 'item', attribute 'sortOrder': 'false' is not a valid value of the atomic type 'xs:integer'." .
            "\nLine: 6\n"
        ],
    ],
];
