<?php declare(strict_types=1);

use Magento\Customer\Model\Attribute\Data\Postcode;
use Magento\Customer\Model\Customer;
use Magento\Framework\Module\Setup\Migration;

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    '$replaceRules' => [
        [
            'table',
            'field',
            Migration::ENTITY_TYPE_MODEL,
            Migration::FIELD_CONTENT_TYPE_PLAIN,
        ],
    ],
    '$tableData' => [
        ['field' => 'customer/customer'],
        ['field' => 'customer/attribute_data_postcode'],
        ['field' => 'customer/attribute_data_postcode::someMethod'],
        ['field' => Customer::class],
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
                'to' => Postcode::class,
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
            Migration::ENTITY_TYPE_MODEL => [
                'customer/customer' => \Magento\Customer\Model\Customer_FROM_MAP::class,
                'customer/attribute_data_postcode' => Postcode::class,
            ],
        ],
    ],
    '$aliasesMap' => [
        Migration::ENTITY_TYPE_MODEL => [
            'customer/customer' => \Magento\Customer\Model\Customer_FROM_MAP::class,
        ],
    ]
];
