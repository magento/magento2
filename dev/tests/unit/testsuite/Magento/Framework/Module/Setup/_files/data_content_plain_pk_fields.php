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
                'to' => 'Magento\Customer\Model\Resource\Attribute\Collection',
                'from' => ['`pk_field1` = ?' => 'pk_value1', '`pk_field2` = ?' => 'pk_value2'],
            ],
        ],
        'aliases_map' => [
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_RESOURCE => [
                'customer/attribute_collection' => 'Magento\Customer\Model\Resource\Attribute\Collection',
            ],
        ],
    ]
];
