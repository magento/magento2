<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'without_type_handle' => [
        '<?xml version="1.0"?><config></config>',
        ["Element 'config': Missing child element(s). Expected is ( type ).\nLine: 1\n"],
    ],
    'cache_config_with_notallowed_attribute' => [
        '<?xml version="1.0"?><config>' .
        '<type name="test" translate="label,description" instance="Class\Name" notallowed="some value">' .
        '<label>Test</label><description>Test</description></type></config>',
        ["Element 'type', attribute 'notallowed': The attribute 'notallowed' is not allowed.\nLine: 1\n"],
    ],
    'cache_config_without_name_attribute' => [
        '<?xml version="1.0"?><config><type translate="label,description" instance="Class\Name">' .
        '<label>Test</label><description>Test</description></type></config>',
        ["Element 'type': The attribute 'name' is required but missing.\nLine: 1\n"],
    ],
    'cache_config_without_instance_attribute' => [
        '<?xml version="1.0"?><config><type name="test" translate="label,description">' .
        '<label>Test</label><description>Test</description></type></config>',
        ["Element 'type': The attribute 'instance' is required but missing.\nLine: 1\n"],
    ],
    'cache_config_without_label_element' => [
        '<?xml version="1.0"?><config><type name="test" translate="label,description" instance="Class\Name">' .
        '<description>Test</description></type></config>',
        ["Element 'type': Missing child element(s). Expected is ( label ).\nLine: 1\n"],
    ],
    'cache_config_without_description_element' => [
        '<?xml version="1.0"?><config><type name="test" translate="label,description" instance="Class\Name">' .
        '<label>Test</label></type></config>',
        ["Element 'type': Missing child element(s). Expected is ( description ).\nLine: 1\n"],
    ],
    'cache_config_without_child_elements' => [
        '<?xml version="1.0"?><config><type name="test" translate="label,description" instance="Class\Name">' .
        '</type></config>',
        ["Element 'type': Missing child element(s). Expected is one of ( label, description ).\nLine: 1\n"],
    ],
    'cache_config_cache_name_not_unique' => [
        '<?xml version="1.0"?><config><type name="test" translate="label,description" instance="Class\Name1">' .
        '<label>Test1</label><description>Test1</description></type>' .
        '<type name="test" translate="label,description" instance="Class\Name2">' .
        '<label>Test2</label><description>Test2</description></type></config>',
        [
            "Element 'type': Duplicate key-sequence ['test'] in unique identity-constraint"
                . " 'uniqueCacheName'.\nLine: 1\n"
        ],
    ],
];
