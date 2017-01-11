<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'disabled_attribute_empty_value' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar" ' .
        'disabled="" title="test"/></resources></acl></config>',
        [
            "Element 'resource', attribute 'disabled': '' is not a valid value of the atomic" .
            " type 'xs:boolean'.\nLine: 1\n"],
    ],
    'disabled_attribute_wrong_type_value' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar" ' .
        'disabled="notBool"  title="test"/></resources></acl></config>',
        [
            "Element 'resource', attribute 'disabled': 'notBool' is not a valid value of the atomic type " .
            "'xs:boolean'.\nLine: 1\n"
        ],
    ],
    'double_acl' => [
        '<?xml version="1.0"?><config><acl><resources></resources></acl><acl/></config>',
        ["Element 'acl': This element is not expected.\nLine: 1\n"],
    ],
    'double_resource' => [
        '<?xml version="1.0"?><config><acl><resources></resources><resources></resources></acl></config>',
        ["Element 'resources': This element is not expected.\nLine: 1\n"],
    ],
    'less_minLength_title_attribute' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar" ' .
        'title="Sh"/></resources></acl></config>',
        [
            "Element 'resource', attribute 'title': [facet 'minLength'] The value 'Sh' has a length of '2'; " .
            "this underruns the allowed minimum length of '3'.\nLine: 1\n",
            "Element 'resource', attribute 'title': 'Sh' is not a valid value of the atomic type" .
            " 'typeTitle'.\nLine: 1\n"
        ],
    ],
    'more_maxLength_title_attribute' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar"' .
        ' title="Lorem ipsum dolor sit amet, consectetur adipisicing"/></resources></acl></config>',
        [
            "Element 'resource', attribute 'title': [facet 'maxLength'] The value 'Lorem ipsum dolor sit amet, " .
            "consectetur adipisicing' has a length of '51'; this exceeds the allowed maximum" .
            " length of '50'.\nLine: 1\n",
            "Element 'resource', attribute 'title': 'Lorem ipsum dolor sit amet, consectetur adipisicing' is not " .
            "a valid value of the atomic type 'typeTitle'.\nLine: 1\n"
        ],
    ],
    'notvalid_id_attribute_value_regexp1' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="test_Value::show_toolbar" title="test"/>' .
        '</resources></acl></config>',
        [
            "Element 'resource', attribute 'id': [facet 'pattern'] The value 'test_Value::show_toolbar' is " .
            "not accepted by the pattern" .
            " '([A-Z]+[a-zA-Z0-9]{1,}){1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.\nLine: 1\n",
            "Element 'resource', attribute 'id': 'test_Value::show_toolbar' is not a valid value of the atomic type " .
            "'typeId'.\nLine: 1\n",
            "Element 'resource', attribute 'id': Warning: No precomputed value available, " .
            "the value was either invalid or " .
            "something strange happend.\nLine: 1\n"
        ],
    ],
    'notvalid_id_attribute_value_regexp2' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_value::show_toolbar" title="test"/>' .
        '</resources></acl></config>',
        [
            "Element 'resource', attribute 'id': [facet 'pattern'] The value 'Test_value::show_toolbar' is not " .
            "accepted by the pattern '([A-Z]+[a-zA-Z0-9]{1,}){1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.\nLine: 1\n",
            "Element 'resource', attribute 'id': 'Test_value::show_toolbar' is not a valid value of the atomic type " .
            "'typeId'.\nLine: 1\n",
            "Element 'resource', attribute 'id': Warning: No precomputed value available, " .
            "the value was either invalid or something strange happend.\nLine: 1\n"
        ],
    ],
    'notvalid_id_attribute_value_regexp3' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="M@#$%^*_Value::show_toolbar" title="test"/>' .
        '</resources></acl></config>',
        [
            "Element 'resource', attribute 'id': [facet 'pattern'] The value 'M@#$%^*_Value::show_toolbar' is not " .
            "accepted by the pattern '([A-Z]+[a-zA-Z0-9]{1,}){1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.\nLine: 1\n",
            "Element 'resource', attribute 'id': 'M@#$%^*_Value::show_toolbar' " .
            "is not a valid value of the atomic type 'typeId'.\nLine: 1\n",
            "Element 'resource', attribute 'id': Warning: No precomputed value available, " .
            "the value was either invalid or something strange happend.\nLine: 1\n"
        ],
    ],
    'notvalid_id_attribute_value_regexp4' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="_Value::show_toolbar" title="test"/>' .
        '</resources></acl></config>',
        [
            "Element 'resource', attribute 'id': [facet 'pattern'] The value '_Value::show_toolbar' is not " .
            "accepted by the pattern '([A-Z]+[a-zA-Z0-9]{1,}){1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.\nLine: 1\n",
            "Element 'resource', attribute 'id': '_Value::show_toolbar' " .
            "is not a valid value of the atomic type 'typeId'.\nLine: 1\n",
            "Element 'resource', attribute 'id': " .
            "Warning: No precomputed value available, the value was either invalid " .
            "or something strange happend.\nLine: 1\n"
        ],
    ],
    'notvalid_id_attribute_value_regexp5' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="Value_::show_toolbar" title="test"/></resources>' .
        '</acl></config>',
        [
            "Element 'resource', attribute 'id': [facet 'pattern'] The value 'Value_::show_toolbar' is not " .
            "accepted by the pattern '([A-Z]+[a-zA-Z0-9]{1,}){1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.\nLine: 1\n",
            "Element 'resource', attribute 'id': 'Value_::show_toolbar' " .
            "is not a valid value of the atomic type 'typeId'.\nLine: 1\n",
            "Element 'resource', attribute 'id': " .
            "Warning: No precomputed value available, the value was either invalid " .
            "or something strange happend.\nLine: 1\n"
        ],
    ],
    'notvalid_id_attribute_value_regexp6' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_value:show_toolbar" title="test"/>' .
        '</resources></acl></config>',
        [
            "Element 'resource', attribute 'id': [facet 'pattern'] The value 'Test_value:show_toolbar' is not " .
            "accepted by the pattern '([A-Z]+[a-zA-Z0-9]{1,}){1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.\nLine: 1\n",
            "Element 'resource', attribute 'id': 'Test_value:show_toolbar' is not a valid value of the atomic " .
            "type 'typeId'.\nLine: 1\n",
            "Element 'resource', attribute 'id': " .
            "Warning: No precomputed value available, the value was either invalid " .
            "or something strange happend.\nLine: 1\n"
        ],
    ],
    'notvalid_id_attribute_value_regexp7' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::" title="test"/></resources>' .
        '</acl></config>',
        [
            "Element 'resource', attribute 'id': [facet 'pattern'] The value 'Test_Value::' is not accepted by " .
            "the pattern '([A-Z]+[a-zA-Z0-9]{1,}){1,}_[A-Z]+[A-Z0-9a-z]{1,}::[A-Za-z_0-9]{1,}'.\nLine: 1\n",
            "Element 'resource', attribute 'id': 'Test_Value::' is not a valid value of the atomic type" .
            " 'typeId'.\nLine: 1\n",
            "Element 'resource', attribute 'id': " .
            "Warning: No precomputed value available, the value was either invalid " .
            "or something strange happend.\nLine: 1\n"
        ],
    ],
    'sortOrder_attribute_empty_value' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar" ' .
        'title="Lorem ipsum" sortOrder="stringValue"/></resources></acl></config>',
        [
            "Element 'resource', attribute 'sortOrder': 'stringValue' is not a valid value of the atomic " .
            "type 'xs:int'.\nLine: 1\n"
        ],
    ],
    'sortOrder_attribute_wrong_type_value' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar"  ' .
        'title="Lorem ipsum" sortOrder=""/></resources></acl></config>',
        ["Element 'resource', attribute 'sortOrder': '' is not a valid value of the atomic type 'xs:int'.\nLine: 1\n"],
    ],
    'with_not_allowed_attribute' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar" title="test" ' .
        'someatrrname="some value"/></resources></acl></config>',
        ["Element 'resource', attribute 'someatrrname': The attribute 'someatrrname' is not allowed.\nLine: 1\n"],
    ],
    'with_two_same_id' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar" ' .
        'title="Lorem ipsum"/><resource id="Test_Value::show_toolbar" title="Lorem ipsum"/>' .
        '</resources></acl></config>',
        [
            "Element 'resource': Duplicate key-sequence ['Test_Value::show_toolbar'] in unique identity-constraint " .
            "'uniqueResourceId'.\nLine: 1\n"
        ],
    ],
    'without_acl' => [
        '<?xml version="1.0"?><config/>',
        ["Element 'config': Missing child element(s). Expected is ( acl ).\nLine: 1\n"],
    ],
    'without_required_id_attribute' => [
        '<?xml version="1.0"?><config><acl><resources><resource title="Notifications"/></resources></acl></config>',
        ["Element 'resource': The attribute 'id' is required but missing.\nLine: 1\n"],
    ],
    'without_resource' => [
        '<?xml version="1.0"?><config><acl/></config>',
        ["Element 'acl': Missing child element(s). Expected is ( resources ).\nLine: 1\n"],
    ],
    'without_title' => [
        '<?xml version="1.0"?><config><acl><resources><resource id="Test_Value::show_toolbar" />' .
        '</resources></acl></config>',
        ["Element 'resource': The attribute 'title' is required but missing.\nLine: 1\n"],
    ],
];
