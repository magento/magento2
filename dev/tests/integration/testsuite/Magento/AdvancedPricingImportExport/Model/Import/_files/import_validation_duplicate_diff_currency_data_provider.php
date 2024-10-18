<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    [
        [
        ],
        [
            [
                'sku' => 'AdvancedPricingSimple 1',
                'tier_price_website' => 'All Websites [USD]',
                'tier_price_customer_group' => 'ALL GROUPS',
                'tier_price_qty' => '5.0000',
                'tier_price' => '300',
                'tier_price_value_type' => 'Fixed',
            ],
            [
                'sku' => 'AdvancedPricingSimple 1',
                'tier_price_website' => 'base',
                'tier_price_customer_group' => 'ALL GROUPS',
                'tier_price_qty' => '5.0000',
                'tier_price' => '25.0000',
                'tier_price_value_type' => 'Discount',
            ]
        ],
        \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
        []
    ],
    [
        [
        ],
        [
            [
                'sku' => 'AdvancedPricingSimple 2',
                'tier_price_website' => 'base',
                'tier_price_customer_group' => 'ALL GROUPS',
                'tier_price_qty' => '10.0000',
                'tier_price' => '450',
                'tier_price_value_type' => 'Fixed',
            ],
            [
                'sku' => 'AdvancedPricingSimple 2',
                'tier_price_website' => 'All Websites [USD]',
                'tier_price_customer_group' => 'ALL GROUPS',
                'tier_price_qty' => '10.0000',
                'tier_price' => '30.0000',
                'tier_price_value_type' => 'Discount',
            ]
        ],
        \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
        []
    ],
    [
        [
            [
                'sku' => 'AdvancedPricingSimple 1',
                'website_id' => 0,
                'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                'qty' => 5,
                'value' => 300,
                'percentage_value' => null
            ]
        ],
        [
            [
                'sku' => 'AdvancedPricingSimple 1',
                'tier_price_website' => 'base',
                'tier_price_customer_group' => 'ALL GROUPS',
                'tier_price_qty' => '5.0000',
                'tier_price' => '25.0000',
                'tier_price_value_type' => 'Discount',
            ]
        ],
        \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
        []
    ],
    [
        [
            [
                'sku' => 'AdvancedPricingSimple 2',
                'website_id' => 'base',
                'customer_group_id' => \Magento\Customer\Model\Group::CUST_GROUP_ALL,
                'qty' => 10,
                'value' => 450,
                'percentage_value' => null
            ]
        ],
        [
            [
                'sku' => 'AdvancedPricingSimple 2',
                'tier_price_website' => 'All Websites [USD]',
                'tier_price_customer_group' => 'ALL GROUPS',
                'tier_price_qty' => '10.0000',
                'tier_price' => '30.0000',
                'tier_price_value_type' => 'Discount',
            ]
        ],
        \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
        []
    ]
];
