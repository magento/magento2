<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require 'cart_rule_free_shipping.php';

$row =
    [
        'name' => 'Free shipping for cart if item price >10',
        'is_active' => 1,
        'customer_group_ids' => [\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON,
        'conditions' => [
            1 =>
                [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
                    'attribute' => null,
                    'operator' => null,
                    'value' => '1',
                    'is_value_processed' => null,
                    'aggregator' => 'all',
                ]

        ],
        'actions' => [
            1 => [
                'type' => Magento\SalesRule\Model\Rule\Condition\Product\Combine::class,
                'attribute' => null,
                'operator' => null,
                'value' => '1',
                'is_value_processed' => null,
                'aggregator' => 'all',
                'conditions' => [
                    [
                        'type' => Magento\SalesRule\Model\Rule\Condition\Product::class,
                        'attribute' => 'quote_item_price',
                        'operator' => '==',
                        'value' => '7',
                        'is_value_processed' => false,
                    ]
                ]
            ]
        ],
        'is_advanced' => 1,
        'simple_action' => 'by_percent',
        'discount_amount' => 0,
        'stop_rules_processing' => 0,
        'discount_qty' => 0,
        'discount_step' => 0,
        'apply_to_shipping' => 1,
        'times_used' => 0,
        'is_rss' => 1,
        'use_auto_generation' => 0,
        'uses_per_coupon' => 0,
        'simple_free_shipping' => 2,

        'website_ids' => [
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getWebsite()->getId()
        ]
    ];
$salesRule->loadPost($row);
$salesRule->save();
