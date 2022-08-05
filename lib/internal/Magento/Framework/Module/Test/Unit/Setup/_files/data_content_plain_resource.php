<?php declare(strict_types=1);

use Magento\Customer\Model\ResourceModel\Attribute\Collection;
use Magento\Framework\Module\Setup\Migration;

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    '$replaceRules' => [
        [
            'table',
            'collection',
            Migration::ENTITY_TYPE_RESOURCE,
            Migration::FIELD_CONTENT_TYPE_PLAIN,
            [],
            'flag = 1',
        ],
    ],
    '$tableData' => [['collection' => 'customer/attribute_collection']],
    '$expected' => [
        'updates' => [
            [
                'table' => 'table',
                'field' => 'collection',
                'to' => Collection::class,
                'from' => ['`collection` = ?' => 'customer/attribute_collection'],
            ],
        ],
        'where' => ['flag = 1'],
        'aliases_map' => [
            Migration::ENTITY_TYPE_RESOURCE => [
                'customer/attribute_collection' => Collection::class,
            ],
        ],
    ]
];
