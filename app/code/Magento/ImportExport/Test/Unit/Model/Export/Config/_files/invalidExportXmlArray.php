<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    'export_entity_name_must_be_unique' => [
        '<?xml version="1.0"?><config><entity name="name_one" entityAttributeFilterType="name_one"/>'
            . '<entity name="name_one" entityAttributeFilterType="name_one"/></config>',
        [
            "Element 'entity': Duplicate key-sequence ['name_one'] in unique identity-constraint " .
            "'uniqueEntityName'.\nLine: 1\n"
        ],
    ],
    'export_fileFormat_name_must_be_unique' => [
        '<?xml version="1.0"?><config><fileFormat name="name_one" /><fileFormat name="name_one" /></config>',
        [
            "Element 'fileFormat': Duplicate key-sequence ['name_one'] in unique identity-constraint " .
            "'uniqueFileFormatName'.\nLine: 1\n"
        ],
    ],
    'attributes_with_type_modelName_and_invalid_value' => [
        '<?xml version="1.0"?><config><entity name="Name/one" model="model_one" '
            . 'entityAttributeFilterType="model_one"/><entityType entity="Name/one" name="name_one" model="1"/>'
            . ' <fileFormat name="name_one" model="1model"/></config>',
        [
            "Element 'entityType', attribute 'model': [facet 'pattern'] The value '1' is not accepted by the " .
            "pattern '([\\\\]?[a-zA-Z_][a-zA-Z0-9_]*)+'.\nLine: 1\n",
            "Element 'fileFormat', attribute 'model': [facet 'pattern'] The value '1model' is not " .
            "accepted by the pattern '([\\\\]?[a-zA-Z_][a-zA-Z0-9_]*)+'.\nLine: 1\n"
        ],
    ],
    'productType_node_with_required_attribute' => [
        '<?xml version="1.0"?><config><entityType entity="name_one" name="name_one" />'
            . '<entityType entity="name_one" model="model" /></config>',
        [
            "Element 'entityType': The attribute 'model' is required but missing.\nLine: 1\n",
            "Element 'entityType': " . "The attribute 'name' is required but missing.\nLine: 1\n"
        ],
    ],
    'fileFormat_node_with_required_attribute' => [
        '<?xml version="1.0"?><config><fileFormat label="name_one" /></config>',
        ["Element 'fileFormat': The attribute 'name' is required but missing.\nLine: 1\n"],
    ],
    'entity_node_with_required_attribute' => [
        '<?xml version="1.0"?><config><entity label="name_one" entityAttributeFilterType="name_one"/></config>',
        ["Element 'entity': The attribute 'name' is required but missing.\nLine: 1\n"],
    ],
    'entity_node_with_missing_filter_type_attribute' => [
        '<?xml version="1.0"?><config><entity label="name_one" name="name_one"/></config>',
        ["Element 'entity': The attribute 'entityAttributeFilterType' is required but missing.\nLine: 1\n"],
    ]
];
