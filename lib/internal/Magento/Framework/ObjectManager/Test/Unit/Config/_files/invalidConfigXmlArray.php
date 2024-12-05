<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'preference_without_required_for_attribute' => [
        '<?xml version="1.0"?><config><preference type="Some_Type_Name" /></config>',
        [
            "Element 'preference': The attribute 'for' is required but missing.\nLine: 1\nThe xml was: \n" .
            "0:<?xml version=\"1.0\"?>\n1:<config><preference type=\"Some_Type_Name\"/></config>\n2:\n"
        ],
    ],
    'preference_without_required_type_attribute' => [
        '<?xml version="1.0"?><config><preference for="Some_For_Name" /></config>',
        [
            "Element 'preference': The attribute 'type' is required but missing.\nLine: 1\nThe xml was: \n" .
            "0:<?xml version=\"1.0\"?>\n1:<config><preference for=\"Some_For_Name\"/></config>\n2:\n"
        ],
    ],
    'preferences_with_same_for_attribute_value' => [
        '<?xml version="1.0"?>
        <config>
            <preference for="Some_For_Name" type="Some_Type_Name" />
            <preference for="Some_For_Name" type="Some_Type_Name" />
        </config>',
        [
            "Element 'preference': Duplicate key-sequence ['Some_For_Name'] in unique identity-constraint " .
            "'uniquePreference'.\nLine: 4\nThe xml was: \n0:<?xml version=\"1.0\"?>\n1:<config>\n" .
            "2:            <preference for=\"Some_For_Name\" type=\"Some_Type_Name\"/>\n" .
            "3:            <preference for=\"Some_For_Name\" type=\"Some_Type_Name\"/>\n4:        </config>\n5:\n"
        ],
    ],
    'preferences_with_forbidden_attribute' => [
        '<?xml version="1.0"?>
        <config><preference for="Some_For_Name" type="Some_Type_Name" forbidden="text"/></config>',
        [
            "Element 'preference', attribute 'forbidden': The attribute 'forbidden' is not allowed.\nLine: 2\n" .
            "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<config><preference for=\"Some_For_Name\" " .
            "type=\"Some_Type_Name\" forbidden=\"text\"/></config>\n2:\n"
        ],
    ],
    'type_without_required_name_attribute' => [
        '<?xml version="1.0"?><config><type /></config>',
        [
            "Element 'type': The attribute 'name' is required but missing.\nLine: 1\nThe xml was: \n" .
            "0:<?xml version=\"1.0\"?>\n1:<config><type/></config>\n2:\n"
        ],
    ],
    'type_with_same_name_attribute_value' => [
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Type_name" />
            <type name="Some_Type_name" />
        </config>',
        [
            "Element 'type': Duplicate key-sequence ['Some_Type_name'] in unique identity-constraint " .
            "'uniqueType'.\nLine: 4\nThe xml was: \n0:<?xml version=\"1.0\"?>\n1:<config>\n" .
            "2:            <type name=\"Some_Type_name\"/>\n3:            <type name=\"Some_Type_name\"/>\n" .
            "4:        </config>\n5:\n"
        ],
    ],
    'type_with_forbidden_attribute' => [
        '<?xml version="1.0"?><config><type name="Some_Name" forbidden="text"/></config>',
        [
            "Element 'type', attribute 'forbidden': The attribute 'forbidden' is not allowed.\nLine: 1\n" .
            "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<config><type name=\"Some_Name\" forbidden=\"text\"/>" .
            "</config>\n2:\n"
        ],
    ],
    'type_shared_attribute_with_forbidden_value' => [
        '<?xml version="1.0"?><config><type name="Some_Name" shared="forbidden"/></config>',
        [
            "Element 'type', attribute 'shared': 'forbidden' is not a valid value of the atomic type " .
            "'xs:boolean'.\nLine: 1\nThe xml was: \n0:<?xml version=\"1.0\"?>\n" .
            "1:<config><type name=\"Some_Name\" shared=\"forbidden\"/></config>\n2:\n"
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
            "Element 'argument', attribute 'shared': 'forbidden' is not a valid value of the atomic type " .
            "'xs:boolean'.\nLine: 5\nThe xml was: \n0:<?xml version=\"1.0\"?>\n" .
            "1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:            <type name=\"Some_Name\">\n3:                <arguments>\n" .
            "4:                    <argument name=\"Param_name\" xsi:type=\"object\" " .
            "shared=\"forbidden\">Object</argument>\n5:                </arguments>\n6:            </type>\n" .
            "7:        </config>\n8:\n"
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
        [
            "Element 'argument', attribute 'forbidden': The attribute 'forbidden' is not allowed.\nLine: 5\n" .
            "The xml was: \n0:<?xml version=\"1.0\"?>\n" .
            "1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:            <type name=\"Some_Name\">\n3:                <arguments>\n" .
            "4:                    <argument name=\"Param_name\" xsi:type=\"object\" " .
            "forbidden=\"text\">Object</argument>\n5:                </arguments>\n6:            </type>\n" .
            "7:        </config>\n8:\n"
        ],
    ],
    'type_plugin_without_required_name_attribute' => [
        '<?xml version="1.0"?><config><type name="Some_Name"><plugin /></type></config>',
        [
            "Element 'plugin': The attribute 'name' is required but missing.\nLine: 1\nThe xml was: \n" .
            "0:<?xml version=\"1.0\"?>\n1:<config><type name=\"Some_Name\"><plugin/></type></config>\n2:\n"
        ],
    ],
    'type_plugin_with_forbidden_attribute' => [
        '<?xml version="1.0"?>
        <config><type name="Some_Name"><plugin name="some_name" forbidden="text" /></type></config>',
        [
            "Element 'plugin', attribute 'forbidden': The attribute 'forbidden' is not allowed.\nLine: 2\n" .
            "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<config><type name=\"Some_Name\">" .
            "<plugin name=\"some_name\" forbidden=\"text\"/></type></config>\n2:\n"
        ],
    ],
    'type_plugin_disabled_attribute_invalid_value' => [
        '<?xml version="1.0"?>
        <config><type name="Some_Name"><plugin name="some_name" disabled="string" /></type></config>',
        [
            "Element 'plugin', attribute 'disabled': 'string' is not a valid value of the atomic type " .
            "'xs:boolean'.\nLine: 2\nThe xml was: \n0:<?xml version=\"1.0\"?>\n" .
            "1:<config><type name=\"Some_Name\"><plugin name=\"some_name\" disabled=\"string\"/></type>" .
            "</config>\n2:\n"
        ],
    ],
    'type_plugin_sortorder_attribute_invalid_value' => [
        '<?xml version="1.0"?>
        <config><type name="Some_Name"><plugin name="some_name" sortOrder="string" /></type></config>',
        [
            "Element 'plugin', attribute 'sortOrder': 'string' is not a valid value of the atomic type " .
            "'xs:int'.\nLine: 2\nThe xml was: \n0:<?xml version=\"1.0\"?>\n" .
            "1:<config><type name=\"Some_Name\"><plugin name=\"some_name\" sortOrder=\"string\"/></type>" .
            "</config>\n2:\n"
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
            "'argumentName'.\nLine: 6\nThe xml was: \n" .
            "1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:            <type name=\"Some_Name\">\n3:                <arguments>\n" .
            "4:                    <argument name=\"same_argument_name\" xsi:type=\"string\">value</argument>\n" .
            "5:                    <argument name=\"same_argument_name\" xsi:type=\"string\">value2</argument>\n" .
            "6:                </arguments>\n7:            </type>\n8:        </config>\n9:\n"
        ],
    ],
    'virtualtype_without_required_name_attribute' => [
        '<?xml version="1.0"?><config><virtualType /></config>',
        [
            "Element 'virtualType': The attribute 'name' is required but missing.\nLine: 1\n" .
            "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<config><virtualType/></config>\n2:\n"
        ],
    ],
    'virtualtype_with_forbidden_shared_attribute_value' => [
        '<?xml version="1.0"?><config><virtualType name="virtual_name" shared="forbidden"/></config>',
        [
            "Element 'virtualType', attribute 'shared': 'forbidden' is not a valid value of the atomic type " .
            "'xs:boolean'.\nLine: 1\nThe xml was: \n0:<?xml version=\"1.0\"?>\n" .
            "1:<config><virtualType name=\"virtual_name\" shared=\"forbidden\"/></config>\n2:\n"
        ],
    ],
    'virtualtype_with_forbidden_attribute' => [
        '<?xml version="1.0"?><config><virtualType name="virtual_name" forbidden="text"/></config>',
        [
            "Element 'virtualType', attribute 'forbidden': The attribute 'forbidden' is not allowed.\nLine: 1\n" .
            "The xml was: \n0:<?xml version=\"1.0\"?>\n1:<config><virtualType name=\"virtual_name\" " .
            "forbidden=\"text\"/></config>\n2:\n"
        ],
    ],
    'virtualtype_with_same_name_attribute_value' => [
        '<?xml version="1.0"?><config><virtualType name="test_name" /><virtualType name="test_name" /></config>',
        [
            "Element 'virtualType': Duplicate key-sequence ['test_name'] in unique identity-constraint " .
            "'uniqueVirtualType'.\nLine: 1\nThe xml was: \n0:<?xml version=\"1.0\"?>\n" .
            "1:<config><virtualType name=\"test_name\"/><virtualType name=\"test_name\"/></config>\n2:\n"
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
            "Element 'argument': Duplicate key-sequence ['same_param_name'] in key identity-constraint " .
            "'argumentName'.\nLine: 6\nThe xml was: \n" .
            "1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:            <virtualType name=\"virtual_name\">\n3:                <arguments>\n" .
            "4:                    <argument name=\"same_param_name\" xsi:type=\"string\">value</argument>\n" .
            "5:                    <argument name=\"same_param_name\" xsi:type=\"string\">value2</argument>\n" .
            "6:                </arguments>\n7:            </virtualType>\n8:        </config>\n9:\n"
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
            "Element 'item', attribute 'sortOrder': 'false' is not a valid value of the atomic type " .
            "'xs:integer'.\nLine: 6\nThe xml was: \n" .
            "1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:            <type name=\"Some_Name\">\n3:                <arguments>\n" .
            "4:                    <argument name=\"sorted_object_list\" xsi:type=\"array\">\n" .
            "5:                        <item name=\"someObject\" xsi:type=\"object\" sortOrder=\"false\">" .
            "Some_Class_Name</item>\n6:                    </argument>\n7:                </arguments>\n" .
            "8:            </type>\n9:        </config>\n10:\n"
        ],
    ],
    'virtualtype with empty_name' => [
        '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <virtualType name="" type="TypeName" shared="true"/>
        </config>',
        [
            "Element 'virtualType', attribute 'name': '' is not a valid value of the atomic type 'phpClassName'.\n" .
            "Line: 2\nThe xml was: \n0:<?xml version=\"1.0\"?>\n" .
            "1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:            <virtualType name=\"\" type=\"TypeName\" shared=\"true\"/>\n" .
            "3:        </config>\n4:\n"
        ],
    ],
    'virtualtype with empty_type' => [
        '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <virtualType name="Name" type="" shared="true"/>
        </config>',
        [
            "Element 'virtualType', attribute 'type': '' is not a valid value of the atomic type 'phpClassName'.\n" .
            "Line: 2\nThe xml was: \n0:<?xml version=\"1.0\"?>\n" .
            "1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:            <virtualType name=\"Name\" type=\"\" shared=\"true\"/>\n3:        </config>\n4:\n"
        ],
    ],
    'virtualtype with invalid_type' => [
        '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <virtualType name="777Digits\\IsNotAllowed" type="TypeName" shared="true"/>
        </config>',
        [
            "Element 'virtualType', attribute 'name': '777Digits\IsNotAllowed' is not a valid value of the atomic " .
            "type 'phpClassName'.\nLine: 2\nThe xml was: \n0:<?xml version=\"1.0\"?>\n" .
            "1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:            <virtualType name=\"777Digits\IsNotAllowed\" type=\"TypeName\" shared=\"true\"/>\n" .
            "3:        </config>\n4:\n"
        ],
    ],
    'virtualtype with digits_and_prefix_slash' => [
        '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <virtualType name="\\777Digits\\IsNotAllowed" type="TypeName" shared="true"/>
        </config>',
        [
            "Element 'virtualType', attribute 'name': [facet 'pattern'] The value '\\777Digits\\IsNotAllowed' " .
            "is not accepted by the pattern '" .
            "(\\\\?[a-zA-Z_\x7f-\xc3\xbf][a-zA-Z0-9_\x7f-\xc3\xbf]*)" .
            "(\\\\[a-zA-Z_\x7f-\xc3\xbf][a-zA-Z0-9_\x7f-\xc3\xbf]*)*'." .
            "\nLine: 2\nThe xml was: \n0:<?xml version=\"1.0\"?>\n" .
            "1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n" .
            "2:            <virtualType name=\"\\777Digits\\IsNotAllowed\" type=\"TypeName\" shared=\"true\"/>\n" .
            "3:        </config>\n4:\n"
        ],
    ],
];
