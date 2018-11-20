<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'type_without_required_name' => [
        '<?xml version="1.0" encoding="UTF-8"?><config><type label="some label" modelInstance="Model_name" /></config>',
        [
            "Element 'type': The attribute 'name' is required but missing.\nLine: 1\n",
            "Element 'type': Not all fields of key identity-constraint 'productTypeKey' evaluate to a node.\nLine: 1\n"
        ],
    ],
    'type_without_required_label' => [
        '<?xml version="1.0" encoding="UTF-8"?><config><type name="some_name" modelInstance="Model_name" /></config>',
        ["Element 'type': The attribute 'label' is required but missing.\nLine: 1\n"],
    ],
    'type_without_required_modelInstance' => [
        '<?xml version="1.0" encoding="UTF-8"?><config><type label="some_label" name="some_name" /></config>',
        ["Element 'type': The attribute 'modelInstance' is required but missing.\nLine: 1\n"],
    ],
    'type_with_invalid_modelinstance_value' => [
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<type name="some_name" label="some_label" modelInstance="model_name" /></config>',
        [
            "Element 'type', attribute 'modelInstance': [facet 'pattern'] The value 'model_name' is not " .
            "accepted by the pattern '[A-Z]+[a-zA-Z0-9_\\\\]+'.\nLine: 1\n",
            "Element 'type', attribute 'modelInstance': 'model_name' is not a valid value of the atomic type" .
            " 'modelName'.\nLine: 1\n",
        ],
    ],
    'type_pricemodel_without_required_instance_attribute' => [
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<type label="some_label" name="some_name" modelInstance="Model_name"><priceModel/></type></config>',
        ["Element 'priceModel': The attribute 'instance' is required but missing.\nLine: 1\n"],
    ],
    'type_indexmodel_without_required_instance_attribute' => [
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<type label="some_label" name="some_name" modelInstance="Model_name"><indexerModel/></type></config>',
        ["Element 'indexerModel': The attribute 'instance' is required but missing.\nLine: 1\n"],
    ],
    'type_stockindexermodel_without_required_instance_attribute' => [
        '<?xml version="1.0" encoding="UTF-8"?><config><type label="some_label" ' .
        'name="some_name" modelInstance="Model_name"><stockIndexerModel/></type></config>',
        ["Element 'stockIndexerModel': The attribute 'instance' is required but missing.\nLine: 1\n"],
    ]
];
