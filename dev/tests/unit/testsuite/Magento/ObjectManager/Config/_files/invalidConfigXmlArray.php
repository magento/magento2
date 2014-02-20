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
return array
    (
    'preference_without_required_for_attribute' => array(
        '<?xml version="1.0"?><config><preference type="Some_Type_Name" /></config>',
        array("Element 'preference': The attribute 'for' is required but missing.")),
    'preference_without_required_type_attribute' => array(
        '<?xml version="1.0"?><config><preference for="Some_For_Name" /></config>',
        array("Element 'preference': The attribute 'type' is required but missing.")),
    'preferences_with_same_for_attribute_value' => array(
        '<?xml version="1.0"?>
        <config>
            <preference for="Some_For_Name" type="Some_Type_Name" />
            <preference for="Some_For_Name" type="Some_Type_Name" />
        </config>',
        array("Element 'preference': Duplicate key-sequence ['Some_For_Name'] in unique "
            . "identity-constraint 'uniquePreference'.")),
    'preferences_with_forbidden_attribute' => array(
        '<?xml version="1.0"?>
        <config><preference for="Some_For_Name" type="Some_Type_Name" forbidden="text"/></config>',
        array("Element 'preference', attribute 'forbidden': The attribute 'forbidden' is not allowed.")),
    'type_without_required_name_attribute' => array(
        '<?xml version="1.0"?><config><type /></config>',
        array("Element 'type': The attribute 'name' is required but missing.")),
    'type_with_same_name_attribute_value' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Type_name" />
            <type name="Some_Type_name" />
        </config>',
        array("Element 'type': Duplicate key-sequence ['Some_Type_name'] in unique identity-constraint 'uniqueType'.")),
    'type_with_forbidden_attribute' => array(
        '<?xml version="1.0"?><config><type name="Some_Name" forbidden="text"/></config>',
        array("Element 'type', attribute 'forbidden': The attribute 'forbidden' is not allowed.")),
    'type_shared_attribute_with_invalid_value' => array(
        '<?xml version="1.0"?><config><type name="Some_Name" shared="test"/></config>',
        array("Element 'type', attribute 'shared': 'test' is not a valid value of the atomic type 'xs:boolean'.")),
    'type_param_value_with_forbidden_attribute' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Type_name">
                <param name="test_param_name"><value forbidden="test" /></param>
            </type>
        </config>',
        array("Element 'value', attribute 'forbidden': The attribute 'forbidden' is not allowed.")),
    'type_param_empty' => array(
        '<?xml version="1.0"?><config><type name="Some_Name"><param name="Param_name" /></type></config>',
        array("Element 'param': Missing child element(s). Expected is one of ( instance, value, array ).")),
    'type_param_without_required_name_attribute' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name"><param><value /></param></type>
        </config>',
        array("Element 'param': The attribute 'name' is required but missing.")),
    'type_param_instance_without_required_type_attribute' => array(
        '<?xml version="1.0"?>
        <config><type name="Some_Name"><param name="Param_name"><instance /></param></type></config>',
        array("Element 'instance': The attribute 'type' is required but missing.")),
    'type_param_instance_with_invalid_shared_value' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name">
                <param name="Param_name">
                    <instance type="Some_type" shared="string" />
                </param>
            </type>
        </config>',
        array("Element 'instance', attribute 'shared': 'string' is not a valid value of the atomic "
            . "type 'xs:boolean'.")),
    'type_instance_with_forbidden_attribute' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name">
                <param name="Param_name">
                    <instance type="Some_type" forbidden="text" />
                </param>
            </type>
        </config>',
        array("Element 'instance', attribute 'forbidden': The attribute 'forbidden' is not allowed.")),
    'type_plugin_without_required_name_attribute' => array(
        '<?xml version="1.0"?><config><type name="Some_Name"><plugin /></type></config>',
        array("Element 'plugin': The attribute 'name' is required but missing.")),
    'type_plugin_with_forbidden_attribute' => array(
        '<?xml version="1.0"?>
        <config><type name="Some_Name"><plugin name="some_name" forbidden="text" /></type></config>',
        array("Element 'plugin', attribute 'forbidden': The attribute 'forbidden' is not allowed.")),
    'type_plugin_disabled_attribute_invalid_value' => array(
        '<?xml version="1.0"?>
        <config><type name="Some_Name"><plugin name="some_name" disabled="string" /></type></config>',
        array("Element 'plugin', attribute 'disabled': 'string' is not a valid value of the atomic "
            . "type 'xs:boolean'.")),
    'type_plugin_sortorder_attribute_invalid_value' => array(
        '<?xml version="1.0"?>
        <config><type name="Some_Name"><plugin name="some_name" sortOrder="string" /></type></config>',
        array("Element 'plugin', attribute 'sortOrder': 'string' is not a valid value of the atomic type 'xs:int'.")),
    'type_same_name_attribute_value' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name" />
            <type name="Some_Name" />
        </config>',
        array("Element 'type': Duplicate key-sequence ['Some_Name'] in unique identity-constraint 'uniqueType'.")),
    'type_value_forbidden_element' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name">
                <param name="test_param_name">
                    <value>
                        <forbidden />
                    </value>
                </param>
            </type>
        </config>',
        array("Element 'value': Element content is not allowed, because the content type is a simple type definition.")
     ),
    'type_param_several_allowed_elements' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name">
                <param name="test_param_name">
                    <value>value</value>
                    <array>
                        <item key="key"><value>value</value></item>
                    </array>
                </param>
            </type>
        </config>',
        array("Element 'array': This element is not expected.")
     ),
    'type_array_empty' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name">
                <param name="test_param_name">
                    <array />
                </param>
            </type>
        </config>',
        array("Element 'array': Missing child element(s). Expected is ( item ).")
     ),
    'type_array_forbidden_argument' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name">
                <param name="test_param_name">
                    <array forbidden="text">
                        <item key="key"><value>value</value></item>
                    </array>
                </param>
            </type>
        </config>',
        array("Element 'array', attribute 'forbidden': The attribute 'forbidden' is not allowed.")
     ),
    'type_array_forbidden_element' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name">
                <param name="test_param_name">
                    <array>
                        <forbidden />
                        <item key="key"><value>value</value></item>
                    </array>
                </param>
            </type>
        </config>',
        array("Element 'forbidden': This element is not expected. Expected is ( item ).")
     ),
    'type_array_item_missed_argument' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name">
                <param name="test_param_name">
                    <array>
                        <item><value>value</value></item>
                    </array>
                </param>
            </type>
        </config>',
        array("Element 'item': The attribute 'key' is required but missing.")
     ),
    'type_array_item_name_argument (difference between item and param)' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name">
                <param name="test_param_name">
                    <array>
                        <item key="key" name="text"><value>value</value></item>
                    </array>
                </param>
            </type>
        </config>',
        array("Element 'item', attribute 'name': The attribute 'name' is not allowed.")
     ),
    'type_array_item_empty_argument' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name">
                <param name="test_param_name">
                    <array>
                        <item key="key" />
                    </array>
                </param>
            </type>
        </config>',
        array("Element 'item': Missing child element(s). Expected is one of ( instance, value, array ).")
     ),
    'type_array_item_forbidden_element' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name">
                <param name="test_param_name">
                    <array>
                        <item key="key"><forbidden>value</forbidden></item>
                    </array>
                </param>
            </type>
        </config>',
        array("Element 'forbidden': This element is not expected. Expected is one of ( instance, value, array ).")
     ),
    'type_array_item_same_keys' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name">
                <param name="test_param_name">
                    <array>
                        <item key="key"><value>value</value></item>
                        <item key="key"><value>value</value></item>
                    </array>
                </param>
            </type>
        </config>',
        array("Element 'item': Duplicate key-sequence ['key'] in unique identity-constraint 'uniqueArrayIndex'.")
     ),
    'type_array_item_same_keys_in_nested_array' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name">
                <param name="test_param_name">
                    <array>
                        <item key="key_outer">
                            <array>
                                <item key="key"><value>value</value></item>
                                <item key="key"><value>value</value></item>
                            </array>
                        </item>
                    </array>
                </param>
            </type>
        </config>',
        array("Element 'item': Duplicate key-sequence ['key'] in unique identity-constraint 'uniqueArrayIndex'.")
     ),
    'type_array_item_value_forbidden_argument' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name">
                <param name="test_param_name">
                    <array>
                        <item key="key"><value forbidden="text">value</value></item>
                    </array>
                </param>
            </type>
        </config>',
        array("Element 'value', attribute 'forbidden': The attribute 'forbidden' is not allowed.")
     ),
    'type_array_item_value_forbidden_element' => array(
        '<?xml version="1.0"?>
        <config>
            <type name="Some_Name">
                <param name="test_param_name">
                    <array>
                        <item key="key"><value><forbidden /></value></item>
                    </array>
                </param>
            </type>
        </config>',
        array("Element 'value': Element content is not allowed, because the content type is a simple type definition.")
     ),
    'virtualtype_without_required_name_attribute' => array(
        '<?xml version="1.0"?><config><virtualType /></config>',
        array("Element 'virtualType': The attribute 'name' is required but missing.")),
    'virtualtype_with_invalid_shared_attribute_value' => array(
        '<?xml version="1.0"?><config><virtualType name="virtual_name" shared="string"/></config>',
        array("Element 'virtualType', attribute 'shared': 'string' is not a valid value of the atomic "
            . "type 'xs:boolean'.")),
    'virtualtype_with_forbidden_attribute' => array(
        '<?xml version="1.0"?><config><virtualType name="virtual_name" forbidden="text"/></config>',
        array("Element 'virtualType', attribute 'forbidden': The attribute 'forbidden' is not allowed.")),
    'virtualtype_with_same_name_attribute_value' => array(
        '<?xml version="1.0"?><config><virtualType name="test_name" /><virtualType name="test_name" /></config>',
        array("Element 'virtualType': Duplicate key-sequence ['test_name'] in unique"
            . " identity-constraint 'uniqueVirtualType'.")),
    'virtualtype_with_same_param_name_attribute' => array(
        '<?xml version="1.0"?>
        <config>
            <virtualType name="virtual_name">
                <param name="same_param_name"><value>value</value></param>
                <param name="same_param_name"><value>value</value></param>
            </virtualType>
        </config>',
        array("Element 'param': Duplicate key-sequence ['same_param_name'] in unique "
            . "identity-constraint 'uniqueVirtualTypeParam'.")),
    );
