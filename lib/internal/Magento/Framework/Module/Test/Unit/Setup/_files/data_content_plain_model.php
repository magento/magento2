<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
        ['field' => \Magento\Customer\Model\Customer::class],
    ],
    '$expected' => [
        'updates' => [
            [
                'table' => 'table',
                'field' => 'field',
                'to' => \Magento\Customer\Model\Customer_FROM_MAP::class,
                'from' => ['`field` = ?' => 'customer/customer'],
            ],
            [
                'table' => 'table',
                'field' => 'field',
                'to' => \Magento\Customer\Model\Attribute\Data\Postcode::class,
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
                'customer/customer' => \Magento\Customer\Model\Customer_FROM_MAP::class,
                'customer/attribute_data_postcode' => \Magento\Customer\Model\Attribute\Data\Postcode::class,
            ],
        ],
    ],
    '$aliasesMap' => [
        \Magento\Framework\Module\Setup\Migration::ENTITY_TYPE_MODEL => [
            'customer/customer' => \Magento\Customer\Model\Customer_FROM_MAP::class,
        ],
    ]
];
