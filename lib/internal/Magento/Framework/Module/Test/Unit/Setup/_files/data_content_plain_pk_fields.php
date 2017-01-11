<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    '$replaceRules' => [
        [
            'table',
            'collection',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_RESOURCE,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['pk_field1', 'pk_field2'],
        ],
    ],
    '$tableData' => [
        ['collection' => 'customer/attribute_collection', 'pk_field1' => 'pk_value1', 'pk_field2' => 'pk_value2'],
    ],
    '$expected' => [
        'updates' => [
            [
                'table' => 'table',
                'field' => 'collection',
                'to' => \Magento\Customer\Model\ResourceModel\Attribute\Collection::class,
                'from' => ['`pk_field1` = ?' => 'pk_value1', '`pk_field2` = ?' => 'pk_value2'],
            ],
        ],
        'aliases_map' => [
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_RESOURCE => [
                'customer/attribute_collection' => \Magento\Customer\Model\ResourceModel\Attribute\Collection::class,
            ],
        ],
    ]
];
