<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$websiteId = $objectManager->get(StoreManagerInterface::class)
    ->getWebsite()
    ->getId();

/** @var Rule $salesRule */
$salesRule = $objectManager->create(Rule::class);
$salesRule->setData(
    [
        'name' => '10% Off on orders with shipping discount',
        'is_active' => 1,
        'customer_group_ids' => [1],
        'coupon_type' => Rule::COUPON_TYPE_NO_COUPON,
        'simple_action' => 'by_percent',
        'discount_amount' => 10,
        'discount_step' => 0,
        'apply_to_shipping' => 1,
        'stop_rules_processing' => 1,
        'website_ids' => [$websiteId],
         'store_labels' => [
            'store_id' => 0,
            'store_label' => 'Discount Label for 10% off',
         ]
    ]
);

$salesRule->getConditions()->loadArray([
    'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
    'attribute' => null,
    'operator' => null,
    'value' => '1',
    'is_value_processed' => null,
    'aggregator' => 'all',
    'conditions' => [
            [
                'type' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                'attribute' => 'base_subtotal',
                'operator' => '>=',
                'value' => '20',
                'is_value_processed' => false,
                'actions' => [
                        [
                            'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Combine::class,
                            'attribute' => null,
                            'operator' => null,
                            'value' => '1',
                            'is_value_processed' => null,
                            'aggregator'=>'all'
                        ],
                    ],
            ],
        ],
]);

$salesRule->save();
