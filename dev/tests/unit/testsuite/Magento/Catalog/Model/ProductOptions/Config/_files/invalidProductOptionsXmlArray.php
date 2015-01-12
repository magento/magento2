<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'options_node_is_required' => [
        '<?xml version="1.0"?><config><inputType name="name_one" /></config>',
        ["Element 'inputType': This element is not expected. Expected is ( option )."],
    ],
    'inputType_node_is_required' => [
        '<?xml version="1.0"?><config><option name="name_one"/></config>',
        ["Element 'option': Missing child element(s). Expected is ( inputType )."],
    ],
    'options_name_must_be_unique' => [
        '<?xml version="1.0"?><config><option name="name_one"><inputType name="name"/>' .
        '</option><option name="name_one"><inputType name="name_two"/></option></config>',
        [
            "Element 'option': Duplicate key-sequence ['name_one'] in unique identity-constraint " .
            "'uniqueOptionName'."
        ],
    ],
    'inputType_name_must_be_unique' => [
        '<?xml version="1.0"?><config><option name="name"><inputType name="name_one"/>' .
        '<inputType name="name_one"/></option></config>',
        [
            "Element 'inputType': Duplicate key-sequence ['name_one'] in unique identity-constraint " .
            "'uniqueInputTypeName'."
        ],
    ],
    'renderer_attribute_with_invalid_value' => [
        '<?xml version="1.0"?><config><option name="name_one" renderer="true12"><inputType name="name_one"/>' .
        '</option></config>',
        [
            "Element 'option', attribute 'renderer': [facet 'pattern'] The value 'true12' is not accepted by the " .
            "pattern '[a-zA-Z_\\\\\\\\]+'.",
            "Element 'option', attribute 'renderer': 'true12' is not a valid value of the atomic" .
            " type 'modelName'."
        ],
    ],
    'disabled_attribute_with_invalid_value' => [
        '<?xml version="1.0"?><config><option name="name_one"><inputType name="name_one" disabled="7"/>' .
        '<inputType name="name_two" disabled="some_string"/></option></config>',
        [
            "Element 'inputType', attribute 'disabled': '7' is not a valid value of the atomic type 'xs:boolean'.",
            "Element 'inputType', attribute 'disabled': 'some_string' is not a valid value of the atomic type " .
            "'xs:boolean'."
        ],
    ]
];
