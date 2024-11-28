<?php declare(strict_types=1);

use Magento\Customer\Model\ResourceModel\Attribute\Collection;
use Magento\Framework\Module\Setup\Migration;

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'replaceRules' => [
        [
            'table',
            'collection',
            Migration::ENTITY_TYPE_RESOURCE,
            Migration::FIELD_CONTENT_TYPE_PLAIN,
            ['pk_field1', 'pk_field2'],
        ],
    ],
    'tableData' => [
        ['collection' => 'customer/attribute_collection', 'pk_field1' => 'pk_value1', 'pk_field2' => 'pk_value2'],
    ],
    'expected' => [
        'updates' => [
            [
                'table' => 'table',
                'field' => 'collection',
                'to' => Collection::class,
                'from' => ['`pk_field1` = ?' => 'pk_value1', '`pk_field2` = ?' => 'pk_value2'],
            ],
        ],
        'aliases_map' => [
            Migration::ENTITY_TYPE_RESOURCE => [
                'customer/attribute_collection' => Collection::class,
            ],
        ],
    ]
];
