<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'without_required_resource_handle' => [
        '<?xml version="1.0"?><config></config>',
        ["Element 'config': Missing child element(s). Expected is ( resource ).\nLine: 1\n"],
    ],
    'resource_without_required_name_attribute' => [
        '<?xml version="1.0"?><config><resource /></config>',
        ["Element 'resource': The attribute 'name' is required but missing.\nLine: 1\n"],
    ],
    'resource_name_attribute_invalid_value' => [
        '<?xml version="1.0"?><config><resource name="testinvalidname$" /></config>',
        [
            "Element 'resource', attribute 'name': [facet 'pattern'] The value 'testinvalidname$' is not accepted" .
            " by the pattern '[A-Za-z_0-9]+'.\nLine: 1\n",
            "Element 'resource', attribute 'name': 'testinvalidname$' is not a valid value of the atomic " .
            "type 'nameIdentifier'.\nLine: 1\n",
            "Element 'resource', attribute 'name': Warning: No precomputed value available, the value was either " .
            "invalid or something strange happend.\nLine: 1\n"
        ],
    ],
    'resource_extends_attribute_invalid_value' => [
        '<?xml version="1.0"?><config><resource name="test_name" extends="test@"/></config>',
        [
            "Element 'resource', attribute 'extends': [facet 'pattern'] The value 'test@' is not accepted " .
            "by the pattern '[A-Za-z_0-9]+'.\nLine: 1\n",
            "Element 'resource', attribute 'extends': 'test@' is not a valid value of the atomic" .
            " type 'nameIdentifier'.\nLine: 1\n"
        ],
    ],
    'resource_connection_attribute_invalid_value' => [
        '<?xml version="1.0"?><config><resource name="test_name" connection="test#"/></config>',
        [
            "Element 'resource', attribute 'connection': [facet 'pattern'] The value 'test#' is not accepted " .
            "by the pattern '[A-Za-z_0-9]+'.\nLine: 1\n",
            "Element 'resource', attribute 'connection': 'test#' is not a valid value of the atomic" .
            " type 'nameIdentifier'.\nLine: 1\n"
        ],
    ],
    'resource_with_notallowed_attribute' => [
        '<?xml version="1.0"?><config><resource name="test_name" notallowed="test" /></config>',
        ["Element 'resource', attribute 'notallowed': The attribute 'notallowed' is not allowed.\nLine: 1\n"],
    ],
    'resource_with_same_name_value' => [
        '<?xml version="1.0"?><config><resource name="test_name" /><resource name="test_name" /></config>',
        [
            "Element 'resource': Duplicate key-sequence ['test_name'] in unique " .
            "identity-constraint 'uniqueResourceName'.\nLine: 1\n"
        ],
    ]
];
