<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Model\GroupManagement;
use Magento\Framework\Registry;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Condition\Product;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$salesRuleFactory = $objectManager->create(RuleFactory::class);
/** @var Rule $salesRule */
$salesRule = $salesRuleFactory->create();
$row =
    [
        'name' => 'TableRate Free shipping if category is 3',
        'is_active' => 1,
        'customer_group_ids' => [GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => Rule::COUPON_TYPE_NO_COUPON,
        'conditions' => [
            1 => [
                    'type' => Rule\Condition\Combine::class,
                    'attribute' => null,
                    'operator' => null,
                    'value' => '1',
                    'is_value_processed' => null,
                    'aggregator' => 'all',
                ]
        ],
        'actions' => [
            1 => [
                'type' => Combine::class,
                'attribute' => null,
                'operator' => null,
                'value' => '1',
                'is_value_processed' => null,
                'aggregator' => 'all',
                'conditions' => [],
                'actions' => [
                   1 => [
                        'type' => Product::class,
                        'attribute' => 'category_ids',
                        'operator' => '==',
                        'value' => '3',
                        'is_value_processed' => false,
                        'attribute_scope' => ''
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
        'simple_free_shipping' => 1,
        'website_ids' => [
            $objectManager->get(
                StoreManagerInterface::class
            )->getWebsite()->getId()
        ]
    ];
$salesRule->loadPost($row);
$salesRule->save();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('cart_rule_free_shipping_by_category');
$registry->register('cart_rule_free_shipping_by_category', $salesRule);
