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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
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
        '<?xml version="1.0"?><config><preference for="Some_For_Name" type="Some_Type_Name" />'
            . '<preference for="Some_For_Name" type="Some_Type_Name" /></config>',
        array("Element 'preference': Duplicate key-sequence ['Some_For_Name'] in unique "
            . "identity-constraint 'uniquePreference'.")),
    'preferences_with_notallowed_attribute' => array(
        '<?xml version="1.0"?><config><preference for="Some_For_Name" type="Some_Type_Name" notallowed="text"/>'
            . '</config>',
        array("Element 'preference', attribute 'notallowed': The attribute 'notallowed' is not allowed.")),
    'type_without_required_name_attribute' => array(
        '<?xml version="1.0"?><config><type /></config>',
        array("Element 'type': The attribute 'name' is required but missing.")),
    'type_with_same_name_attribute_value' => array(
        '<?xml version="1.0"?><config><type name="Some_Type_name" /><type name="Some_Type_name" /></config>',
        array("Element 'type': Duplicate key-sequence ['Some_Type_name'] in unique identity-constraint 'uniqueType'.")),
    'type_with_notallowed_attribute' => array(
        '<?xml version="1.0"?><config><type name="Some_Name" notallowed="text"/></config>',
        array("Element 'type', attribute 'notallowed': The attribute 'notallowed' is not allowed.")),
    'type_shared_attribute_with_invalid_value' => array(
        '<?xml version="1.0"?><config><type name="Some_Name" shared="test"/></config>',
        array("Element 'type', attribute 'shared': 'test' is not a valid value of the atomic type 'xs:boolean'.")),
    'type_param_value_with_notallowed_attribute' => array(
        '<?xml version="1.0"?><config><type name="Some_Type_name"><param name="test_param_name" >'
            . '<value notallowed="test" /></param></type></config>',
        array("Element 'value', attribute 'notallowed': The attribute 'notallowed' is not allowed.")),
    'type_param_without_required_name_attribute' => array(
        '<?xml version="1.0"?><config><type name="Some_Name"><param /></type></config>',
        array("Element 'param': The attribute 'name' is required but missing.")),
    'type_param_instance_without_required_type_attribute' => array(
        '<?xml version="1.0"?><config><type name="Some_Name"><param name="Pram_Name"><instance /></param></type>'
            . '</config>',
        array("Element 'instance': The attribute 'type' is required but missing.")),
    'type_param_instance_with_invalid_shared_value' => array(
        '<?xml version="1.0"?><config><type name="Some_Name"><param name="Pram_Name">'
            . '<instance type="Some_type" shared="string" /></param></type></config>',
        array("Element 'instance', attribute 'shared': 'string' is not a valid value of the atomic "
            . "type 'xs:boolean'.")),
    'type_instance_with_notallowed_attribute' => array(
        '<?xml version="1.0"?><config><type name="Some_Name"><param name="Pram_Name">'
            . '<instance type="Some_type" notallowed="text" /></param></type></config>',
        array("Element 'instance', attribute 'notallowed': The attribute 'notallowed' is not allowed.")),
    'type_plugin_without_required_name_attribute' => array(
        '<?xml version="1.0"?><config><type name="Some_Name"><plugin /></type></config>',
        array("Element 'plugin': The attribute 'name' is required but missing.")),
    'type_plugin_with_notallowed_attribute' => array(
        '<?xml version="1.0"?><config><type name="Some_Name"><plugin name="some_name" notallowed="text" />'
            . '</type></config>',
        array("Element 'plugin', attribute 'notallowed': The attribute 'notallowed' is not allowed.")),
    'type_plugin_disabled_attribute_invalid_value' => array(
        '<?xml version="1.0"?><config><type name="Some_Name"><plugin name="some_name" disabled="string" />'
            . '</type></config>',
        array("Element 'plugin', attribute 'disabled': 'string' is not a valid value of the atomic "
            . "type 'xs:boolean'.")),
    'type_plugin_sortorder_attribute_invalid_value' => array(
        '<?xml version="1.0"?><config><type name="Some_Name"><plugin name="some_name" sortOrder="string" />'
            . '</type></config>',
        array("Element 'plugin', attribute 'sortOrder': 'string' is not a valid value of the atomic type 'xs:int'.")),
    'type_same_name_attribute_value' => array(
        '<?xml version="1.0"?><config><type name="Some_Name" /><type name="Some_Name" /></config>',
        array("Element 'type': Duplicate key-sequence ['Some_Name'] in unique identity-constraint 'uniqueType'.")),
    'virtualtype_without_required_name_attribute' => array(
        '<?xml version="1.0"?><config><virtualType /></config>',
        array("Element 'virtualType': The attribute 'name' is required but missing.")),
    'virtualtype_with_invalid_shared_attribute_value' => array(
        '<?xml version="1.0"?><config><virtualType name="virtual_name" shared="string"/></config>',
        array("Element 'virtualType', attribute 'shared': 'string' is not a valid value of the atomic "
            . "type 'xs:boolean'.")),
    'virtualtype_with_notallowed_attribute' => array(
        '<?xml version="1.0"?><config><virtualType name="virtual_name" notallowed="text"/></config>',
        array("Element 'virtualType', attribute 'notallowed': The attribute 'notallowed' is not allowed.")),
    'virtualtype_with_same_name_attribute_value' => array(
        '<?xml version="1.0"?><config><virtualType name="test_name" /><virtualType name="test_name" /></config>',
        array("Element 'virtualType': Duplicate key-sequence ['test_name'] in unique"
            . " identity-constraint 'uniqueVirtualType'.")),
    'virtualtype_with_same_param_name_attribute' => array(
        '<?xml version="1.0"?><config><virtualType name="virtual_name"><param name="same_param_name" />'
            . '<param name="same_param_name" /></virtualType></config>',
        array("Element 'param': Duplicate key-sequence ['same_param_name'] in unique "
            . "identity-constraint 'uniqueVirtualTypeParam'.")),
    );
