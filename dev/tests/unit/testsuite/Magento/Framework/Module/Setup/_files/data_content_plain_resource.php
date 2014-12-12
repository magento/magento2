<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
                'to' => 'Magento\Customer\Model\Resource\Attribute\Collection',
                'from' => ['`collection` = ?' => 'customer/attribute_collection'],
            ],
        ],
        'where' => ['flag = 1'],
        'aliases_map' => [
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_RESOURCE => [
                'customer/attribute_collection' => 'Magento\Customer\Model\Resource\Attribute\Collection',
            ],
        ],
    ]
];
