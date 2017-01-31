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
                'to' => 'Magento\Customer\Model\ResourceModel\Attribute\Collection',
                'from' => ['`collection` = ?' => 'customer/attribute_collection'],
            ],
        ],
        'where' => ['flag = 1'],
        'aliases_map' => [
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_RESOURCE => [
                'customer/attribute_collection' => 'Magento\Customer\Model\ResourceModel\Attribute\Collection',
            ],
        ],
    ]
];
