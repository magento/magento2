<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    '$replaceRules' => [
        [
            'table',
            'field',
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL,
            \Magento\Framework\Module\Setup\Migration::FIELD_CONTENT_TYPE_PLAIN,
        ],
    ],
    '$tableData' => [
        ['field' => 'customer/customer'],
        ['field' => 'customer/attribute_data_postcode'],
        ['field' => 'customer/attribute_data_postcode::someMethod'],
        ['field' => 'Magento\Customer\Model\Customer'],
    ],
    '$expected' => [
        'updates' => [
            [
                'table' => 'table',
                'field' => 'field',
                'to' => 'Magento\Customer\Model\Customer_FROM_MAP',
                'from' => ['`field` = ?' => 'customer/customer'],
            ],
            [
                'table' => 'table',
                'field' => 'field',
                'to' => 'Magento\Customer\Model\Attribute\Data\Postcode',
                'from' => ['`field` = ?' => 'customer/attribute_data_postcode']
            ],
            [
                'table' => 'table',
                'field' => 'field',
                'to' => 'Magento\Customer\Model\Attribute\Data\Postcode::someMethod',
                'from' => ['`field` = ?' => 'customer/attribute_data_postcode::someMethod']
            ],
        ],
        'aliases_map' => [
            \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL => [
                'customer/customer' => 'Magento\Customer\Model\Customer_FROM_MAP',
                'customer/attribute_data_postcode' => 'Magento\Customer\Model\Attribute\Data\Postcode',
            ],
        ],
    ],
    '$aliasesMap' => [
        \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL => [
            'customer/customer' => 'Magento\Customer\Model\Customer_FROM_MAP',
        ],
    ]
];
