<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'type_without_required_name' => [
        '<?xml version="1.0" encoding="UTF-8"?><config><type label="some label" modelInstance="model_name" /></config>',
        [
            "Element 'type': The attribute 'name' is required but missing.\nLine: 1\n",
            "Element 'type': Not all fields of key identity-constraint 'productTypeKey' evaluate to a node.\nLine: 1\n"
        ],
    ],
    'type_without_required_label' => [
        '<?xml version="1.0" encoding="UTF-8"?><config><type name="some_name" modelInstance="model_name" /></config>',
        ["Element 'type': The attribute 'label' is required but missing.\nLine: 1\n"],
    ],
    'type_without_required_modelInstance' => [
        '<?xml version="1.0" encoding="UTF-8"?><config><type label="some_label" name="some_name" /></config>',
        ["Element 'type': The attribute 'modelInstance' is required but missing.\nLine: 1\n"],
    ],
    'type_pricemodel_without_required_instance_attribute' => [
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<type label="some_label" name="some_name" modelInstance="model_name"><priceModel/></type></config>',
        ["Element 'priceModel': The attribute 'instance' is required but missing.\nLine: 1\n"],
    ],
    'type_indexmodel_without_required_instance_attribute' => [
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<type label="some_label" name="some_name" modelInstance="model_name"><indexerModel/></type></config>',
        ["Element 'indexerModel': The attribute 'instance' is required but missing.\nLine: 1\n"],
    ],
    'type_stockindexermodel_without_required_instance_attribute' => [
        '<?xml version="1.0" encoding="UTF-8"?><config><type label="some_label" ' .
        'name="some_name" modelInstance="model_name"><stockIndexerModel/></type></config>',
        ["Element 'stockIndexerModel': The attribute 'instance' is required but missing.\nLine: 1\n"],
    ]
];
