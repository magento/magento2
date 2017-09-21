<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'config_only_with_entity_node' => [
        '<?xml version="1.0"?><config><entity type="type_one" /></config>',
        ["Element 'entity': Missing child element(s). Expected is ( attribute ).\nLine: 1\n"],
    ],
    'field_code_must_be_unique' => [
        '<?xml version="1.0"?><config><entity type="type_one"><attribute code="code_one"><field code="code_one_one" ' .
        'locked="true" /><field code="code_one_one" locked="true" /></attribute></entity></config>',
        [
            "Element 'field': Duplicate key-sequence ['code_one_one'] in unique identity-constraint " .
            "'uniqueFieldCode'.\nLine: 1\n"
        ],
    ],
    'type_attribute_is_required' => [
        '<?xml version="1.0"?><config><entity><attribute code="code_one"><field code="code_one_one" ' .
        'locked="true" /></attribute></entity></config>',
        ["Element 'entity': The attribute 'type' is required but missing.\nLine: 1\n"],
    ],
    'attribute_without_required_attributes' => [
        '<?xml version="1.0"?><config><entity type="name"><attribute><field code="code_one_one" ' .
        'locked="true" /></attribute></entity></config>',
        ["Element 'attribute': The attribute 'code' is required but missing.\nLine: 1\n"],
    ],
    'field_node_without_required_attributes' => [
        '<?xml version="1.0"?><config><entity type="name"><attribute code="code"><field code="code_one_one" />' .
        '<field locked="true"/></attribute></entity></config>',
        [
            "Element 'field': The attribute 'locked' is required but missing.\nLine: 1\n",
            "Element 'field': The attribute " . "'code' is required but missing.\nLine: 1\n"
        ],
    ],
    'locked_attribute_with_invalid_value' => [
        '<?xml version="1.0"?><config><entity type="name"><attribute code="code"><field code="code_one" locked="7" />' .
        '<field code="code_one" locked="one_one" /></attribute></entity></config>',
        [
            "Element 'field', attribute 'locked': '7' is not a valid value of the atomic type" .
            " 'xs:boolean'.\nLine: 1\n",
            "Element 'field', attribute 'locked': 'one_one' is not a valid value of the atomic type" .
            " 'xs:boolean'.\nLine: 1\n",
            "Element 'field': Duplicate key-sequence ['code_one'] in unique identity-constraint" .
            " 'uniqueFieldCode'.\nLine: 1\n"
        ],
    ],
    'attribute_with_type_identifierType_with_invalid_value' => [
        '<?xml version="1.0"?><config><entity type="Name"><attribute code="code1"><field code="code_one" ' .
        'locked="true" /><field code="code::one" locked="false" /></attribute></entity></config>',
        [
            "Element 'entity', attribute 'type': [facet 'pattern'] The value 'Name' is not accepted by the pattern " .
            "'[a-z_]+'.\nLine: 1\n",
            "Element 'entity', attribute 'type': 'Name' is not a valid value of the atomic type " .
            "'identifierType'.\nLine: 1\n",
            "Element 'entity', attribute 'type': Warning: No precomputed value available, the value" .
            " was either invalid or something strange happend.\nLine: 1\n",
            "Element 'attribute', attribute 'code': [facet " .
            "'pattern'] The value 'code1' is not accepted by the pattern '[a-z_]+'.\nLine: 1\n",
            "Element 'attribute', attribute " .
            "'code': 'code1' is not a valid value of the atomic type 'identifierType'.\nLine: 1\n",
            "Element 'attribute', attribute " .
            "'code': Warning: No precomputed value available, " .
            "the value was either invalid or something strange happend.\nLine: 1\n",
            "Element 'field', attribute 'code': [facet 'pattern'] " .
            "The value 'code::one' is not accepted by the pattern '" .
            "[a-z_]+'.\nLine: 1\n",
            "Element 'field', attribute 'code': 'code::one' is not a valid value of the atomic type " .
            "'identifierType'.\nLine: 1\n",
            "Element 'field', attribute 'code': Warning: No precomputed value available, the value " .
            "was either invalid or something strange happend.\nLine: 1\n"
        ],
    ]
];
