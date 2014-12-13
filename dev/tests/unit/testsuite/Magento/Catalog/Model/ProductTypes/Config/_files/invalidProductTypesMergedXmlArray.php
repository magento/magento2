<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
return [
    'type_without_required_name' => [
        '<?xml version="1.0" encoding="UTF-8"?><config><type label="some label" modelInstance="model_name" /></config>',
        [
            "Element 'type': The attribute 'name' is required but missing.",
            "Element 'type': Not all fields of key identity-constraint 'productTypeKey' evaluate to a node."
        ],
    ],
    'type_without_required_label' => [
        '<?xml version="1.0" encoding="UTF-8"?><config><type name="some_name" modelInstance="model_name" /></config>',
        ["Element 'type': The attribute 'label' is required but missing."],
    ],
    'type_without_required_modelInstance' => [
        '<?xml version="1.0" encoding="UTF-8"?><config><type label="some_label" name="some_name" /></config>',
        ["Element 'type': The attribute 'modelInstance' is required but missing."],
    ],
    'type_pricemodel_without_required_instance_attribute' => [
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<type label="some_label" name="some_name" modelInstance="model_name"><priceModel/></type></config>',
        ["Element 'priceModel': The attribute 'instance' is required but missing."],
    ],
    'type_indexmodel_without_required_instance_attribute' => [
        '<?xml version="1.0" encoding="UTF-8"?><config>' .
        '<type label="some_label" name="some_name" modelInstance="model_name"><indexerModel/></type></config>',
        ["Element 'indexerModel': The attribute 'instance' is required but missing."],
    ],
    'type_stockindexermodel_without_required_instance_attribute' => [
        '<?xml version="1.0" encoding="UTF-8"?><config><type label="some_label" ' .
        'name="some_name" modelInstance="model_name"><stockIndexerModel/></type></config>',
        ["Element 'stockIndexerModel': The attribute 'instance' is required but missing."],
    ]
];
