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
    'disabled_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar" ' .
        'disabled=""/></resources></acl></config>',
        array("Element 'resource', attribute 'disabled': '' is not a valid value of the atomic type 'xs:boolean'.")
    ),
    'disabled_attribute_wrong_type_value' => array(
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar" ' .
        'disabled="notBool"/></resources></acl></config>',
        array(
            "Element 'resource', attribute 'disabled': 'notBool' is not a valid value of the atomic type " .
            "'xs:boolean'."
        )
    ),
    'double_acl' => array(
        '<?xml version="1.0"?><config><acl><resources></resources></acl><acl/></config>',
        array("Element 'acl': This element is not expected.")
    ),
    'double_resource' => array(
        '<?xml version="1.0"?><config><acl><resources></resources><resources></resources></acl></config>',
        array("Element 'resources': This element is not expected.")
    ),
    'less_minLength_title_attribute' => array(
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar" ' .
        'title="Sh"/></resources></acl></config>',
        array(
            "Element 'resource', attribute 'title': [facet 'minLength'] The value 'Sh' has a length of '2'; " .
            "this underruns the allowed minimum length of '3'.",
            "Element 'resource', attribute 'title': 'Sh' is not a valid value of the atomic type 'typeTitle'."
        )
    ),
    'more_maxLength_title_attribute' => array(
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar"' .
        ' title="Lorem ipsum dolor sit amet, consectetur adipisicing"/></resources></acl></config>',
        array(
            "Element 'resource', attribute 'title': [facet 'maxLength'] The value 'Lorem ipsum dolor sit amet, " .
            "consectetur adipisicing' has a length of '51'; this exceeds the allowed maximum length of '50'.",
            "Element 'resource', attribute 'title': 'Lorem ipsum dolor sit amet, consectetur adipisicing' is not " .
            "a valid value of the atomic type 'typeTitle'."
        )
    ),
    'notvalid_id_attribute_value_regexp1' => array(
        '<?xml version="1.0"?><config><acl><resources><resource id="test_Value::show_toolbar"/>' .
        '</resources></acl></config>',
        array(
            "Element 'resource', attribute 'id': [facet 'pattern'] The value 'test_Value::show_toolbar' is " .
            "not accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'resource', attribute 'id': 'test_Value::show_toolbar' is not a valid value of the atomic type " .
            "'typeId'.",
            "Element 'resource', attribute 'id': Warning: No precomputed value available, ".
            "the value was either invalid or " .
            "something strange happend."
        )
    ),
    'notvalid_id_attribute_value_regexp2' => array(
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_value::show_toolbar"/>' .
        '</resources></acl></config>',
        array(
            "Element 'resource', attribute 'id': [facet 'pattern'] The value 'Test_value::show_toolbar' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'resource', attribute 'id': 'Test_value::show_toolbar' is not a valid value of the atomic type " .
            "'typeId'.",
            "Element 'resource', attribute 'id': Warning: No precomputed value available, ".
            "the value was either invalid " .
            "or something strange happend."
        )
    ),
    'notvalid_id_attribute_value_regexp3' => array(
        '<?xml version="1.0"?><config><acl><resources><resource id="M@#$%^*_Value::show_toolbar"/>' .
        '</resources></acl></config>',
        array(
            "Element 'resource', attribute 'id': [facet 'pattern'] The value 'M@#$%^*_Value::show_toolbar' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'resource', attribute 'id': 'M@#$%^*_Value::show_toolbar' ".
            "is not a valid value of the atomic type " .
            "'typeId'.",
            "Element 'resource', attribute 'id': Warning: No precomputed value available, ".
            "the value was either invalid " .
            "or something strange happend."
        )
    ),
    'notvalid_id_attribute_value_regexp4' => array(
        '<?xml version="1.0"?><config><acl><resources><resource id="_Value::show_toolbar"/>' .
        '</resources></acl></config>',
        array(
            "Element 'resource', attribute 'id': [facet 'pattern'] The value '_Value::show_toolbar' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'resource', attribute 'id': '_Value::show_toolbar' ".
            "is not a valid value of the atomic type 'typeId'.",
            "Element 'resource', attribute 'id': ".
            "Warning: No precomputed value available, the value was either invalid " .
            "or something strange happend."
        )
    ),
    'notvalid_id_attribute_value_regexp5' => array(
        '<?xml version="1.0"?><config><acl><resources><resource id="Value_::show_toolbar"/></resources>' .
        '</acl></config>',
        array(
            "Element 'resource', attribute 'id': [facet 'pattern'] The value 'Value_::show_toolbar' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'resource', attribute 'id': 'Value_::show_toolbar' ".
            "is not a valid value of the atomic type 'typeId'.",
            "Element 'resource', attribute 'id': ".
            "Warning: No precomputed value available, the value was either invalid " .
            "or something strange happend."
        )
    ),
    'notvalid_id_attribute_value_regexp6' => array(
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_value:show_toolbar"/>' .
        '</resources></acl></config>',
        array(
            "Element 'resource', attribute 'id': [facet 'pattern'] The value 'Test_value:show_toolbar' is not " .
            "accepted by the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'resource', attribute 'id': 'Test_value:show_toolbar' is not a valid value of the atomic " .
            "type 'typeId'.",
            "Element 'resource', attribute 'id': ".
            "Warning: No precomputed value available, the value was either invalid " .
            "or something strange happend."
        )
    ),
    'notvalid_id_attribute_value_regexp7' => array(
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::"/></resources>' . '</acl></config>',
        array(
            "Element 'resource', attribute 'id': [facet 'pattern'] The value 'Test_Value::' is not accepted by " .
            "the pattern '[A-Z]+[a-z0-9]{1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.",
            "Element 'resource', attribute 'id': 'Test_Value::' is not a valid value of the atomic type 'typeId'.",
            "Element 'resource', attribute 'id': ".
            "Warning: No precomputed value available, the value was either invalid " .
            "or something strange happend."
        )
    ),
    'sortOrder_attribute_empty_value' => array(
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar" ' .
        'title="Lorem ipsum" sortOrder="stringValue"/></resources></acl></config>',
        array(
            "Element 'resource', attribute 'sortOrder': 'stringValue' is not a valid value of the atomic " .
            "type 'xs:int'."
        )
    ),
    'sortOrder_attribute_wrong_type_value' => array(
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar" ' .
        'title="Lorem ipsum" sortOrder=""/></resources></acl></config>',
        array("Element 'resource', attribute 'sortOrder': '' is not a valid value of the atomic type 'xs:int'.")
    ),
    'with_not_allowed_attribute' => array(
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar" ' .
        'someatrrname="some value"/></resources></acl></config>',
        array("Element 'resource', attribute 'someatrrname': The attribute 'someatrrname' is not allowed.")
    ),
    'with_two_same_id' => array(
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar" ' .
        'title="Lorem ipsum"/><resource id="Test_Value::show_toolbar" title="Lorem ipsum"/>' .
        '</resources></acl></config>',
        array(
            "Element 'resource': Duplicate key-sequence ['Test_Value::show_toolbar'] in unique identity-constraint " .
            "'uniqueResourceId'."
        )
    ),
    'without_acl' => array(
        '<?xml version="1.0"?><config/>',
        array("Element 'config': Missing child element(s). Expected is ( acl ).")
    ),
    'without_required_id_attribute' => array(
        '<?xml version="1.0"?><config><acl><resources><resource title="Notifications"/></resources></acl></config>',
        array("Element 'resource': The attribute 'id' is required but missing.")
    ),
    'without_resource' => array(
        '<?xml version="1.0"?><config><acl/></config>',
        array("Element 'acl': Missing child element(s). Expected is ( resources ).")
    )
);
