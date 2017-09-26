<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'entity_without_required_name' => [
        '<?xml version="1.0"?><config><entity  label="test" model="test" behaviorModel="test" /></config>',
        ["Element 'entity': The attribute 'name' is required but missing.\nLine: 1\n"],
    ],
    'entity_without_required_label' => [
        '<?xml version="1.0"?><config><entity name="test_name" model="test" behaviorModel="test" /></config>',
        ["Element 'entity': The attribute 'label' is required but missing.\nLine: 1\n"],
    ],
    'entity_without_required_behaviormodel' => [
        '<?xml version="1.0"?><config><entity name="test_name" label="test_label" model="test" /></config>',
        ["Element 'entity': The attribute 'behaviorModel' is required but missing.\nLine: 1\n"],
    ],
    'entity_without_required_model' => [
        '<?xml version="1.0"?><config><entity name="test_name" label="test_label" behaviorModel="test" /></config>',
        ["Element 'entity': The attribute 'model' is required but missing.\nLine: 1\n"],
    ],
    'entity_with_notallowed_atrribute' => [
        '<?xml version="1.0"?><config><entity name="test_name" label="test_label" ' .
        'model="test" behaviorModel="test" notallowed="text" /></config>',
        ["Element 'entity', attribute 'notallowed': The attribute 'notallowed' is not allowed.\nLine: 1\n"],
    ],
    'entity_model_with_invalid_value' => [
        '<?xml version="1.0"?><config><entity name="test_name" label="test_label" model="afwer-blam" ' .
        'behaviorModel="test" /></config>',
        [
            "Element 'entity', attribute 'model': [facet 'pattern'] The value 'afwer-blam' is not " .
            "accepted by the pattern '[A-Za-z_0-9\\\\]+'.\nLine: 1\n",
            "Element 'entity', attribute 'model': 'afwer-blam' is not a valid value of the atomic type" .
            " 'modelName'.\nLine: 1\n"
        ],
    ],
    'entity_behaviorModel_with_invalid_value' => [
        '<?xml version="1.0"?><config><entity name="test_name" label="test_label" model="test" ' .
        'behaviorModel="6-66" /></config>',
        [
            "Element 'entity', attribute 'behaviorModel': [facet 'pattern'] The value '6-66' is not accepted by " .
            "the pattern '[A-Za-z_0-9\\\\]+'.\nLine: 1\n",
            "Element 'entity', attribute 'behaviorModel': '6-66' is not a valid value of the atomic type" .
            " 'modelName'.\nLine: 1\n"
        ],
    ]
];
