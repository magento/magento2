<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'options_node_is_required' => [
        '<?xml version="1.0"?><config><inputType name="name_one" label="Label One"/></config>',
        ["Element 'inputType': This element is not expected. Expected is ( option )."],
    ],
    'inputType_node_is_required' => [
        '<?xml version="1.0"?><config><option name="name_one" label="Label One" renderer="one"/></config>',
        ["Element 'option': Missing child element(s). Expected is ( inputType )."],
    ],
    'options_node_without_required_attributes' => [
        '<?xml version="1.0"?><config><option name="name_one" label="label one"><inputType name="name" label="one"/>' .
        '</option><option name="name_two" renderer="renderer"><inputType name="name_two" label="one" /></option>' .
        '<option label="label three" renderer="renderer"><inputType name="name_one" label="one"/></option></config>',
        [
            "Element 'option': The attribute 'renderer' is required but missing.",
            "Element 'option': The attribute " . "'label' is required but missing.",
            "Element 'option': The attribute 'name' is required but missing."
        ],
    ],
    'inputType_node_without_required_attributes' => [
        '<?xml version="1.0"?><config><option name="name_one" label="label one" renderer="renderer">' .
        '<inputType name="name_one"/></option><option name="name_two" renderer="renderer" label="label">' .
        '<inputType label="name_two"/></option></config>',
        [
            "Element 'inputType': The attribute 'label' is required but missing.",
            "Element 'inputType': The " . "attribute 'name' is required but missing."
        ],
    ]
];
