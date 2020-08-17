<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Model\GroupManagement;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$websiteId = Bootstrap::getObjectManager()->get(StoreManagerInterface::class)
    ->getWebsite()
    ->getId();

/** @var Rule $salesRule */
$salesRule = $objectManager->create(Rule::class);
$salesRule->setData(
    [
        'name' => '10% Off on orders with two items',
        'is_active' => 1,
        'customer_group_ids' => [GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => Rule::COUPON_TYPE_NO_COUPON,
        'simple_action' => 'by_percent',
        'discount_amount' => 10,
        'discount_step' => 0,
        'stop_rules_processing' => 1,
        'is_advanced' => 1,
        'website_ids' => [$websiteId],
        'store_labels' => [

            'store_id' => 0,
            'store_label' => '10% off with two items_Label',

        ]
    ]
);

$salesRule->getConditions()->loadArray(
    [
    'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
    'attribute' => null,
    'operator' => null,
    'value' => '1',
    'is_value_processed' => null,
    'aggregator' => 'all',
    'conditions' =>
        [
            [
                'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                'attribute' => null,
                'operator' => null,
                'value' => '1',
                'is_value_processed' => null,
                'aggregator' => 'all',
                'conditions' =>
                    [
                        [
                            'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                            'attribute' => 'quote_item_qty',
                            'operator' => '>=',
                            'value' => '2',
                            'is_value_processed' => false,
                        ],
                    ],
            ],
        ],
    ]
);

$salesRule->save();
