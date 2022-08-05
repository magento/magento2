<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Customer\Model\GroupManagement;
use Magento\Framework\Registry;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Condition\Address;
use Magento\SalesRule\Model\Rule\Condition\Combine;
use Magento\SalesRule\Model\Rule\Condition\Product;
use Magento\SalesRule\Model\Rule\Condition\Product\Found;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Rule $rule */
$salesRule = Bootstrap::getObjectManager()->create(Rule::class);
$salesRule->setData(
    [
        'name' => '50% Off on Large Orders',
        'is_active' => 1,
        'customer_group_ids' => [GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => Rule::COUPON_TYPE_NO_COUPON,
        'simple_action' => 'by_percent',
        'discount_amount' => 50,
        'discount_step' => 0,
        'stop_rules_processing' => 1,
        'website_ids' => [
            Bootstrap::getObjectManager()->get(
                StoreManagerInterface::class
            )->getWebsite()->getId()
        ]
    ]
);

$salesRule->getConditions()->loadArray([
    'type' => Combine::class,
    'attribute' => null,
    'operator' => null,
    'value' => '1',
    'is_value_processed' => null,
    'aggregator' => 'any',
    'conditions' =>
        [
                [
                    'type' => Found::class,
                    'attribute' => null,
                    'operator' => null,
                    'value' => '1',
                    'is_value_processed' => null,
                    'aggregator' => 'all',
                    'conditions' =>
                        [
                                [
                                    'type' => Product::class,
                                    'attribute' => 'category_ids',
                                    'operator' => '==',
                                    'value' => '2',
                                    'is_value_processed' => false,
                                ],
                        ],
                ],
                [
                    'type' => Address::class,
                    'attribute' => 'payment_method',
                    'operator' => '==',
                    'value' => 'payflowpro'
                ],                [
                    'type' => Address::class,
                    'attribute' => 'shipping_method',
                    'operator' => '==',
                    'value' => 'fedex_FEDEX_2_DAY'
                ],
                [
                    'type' => Address::class,
                    'attribute' => 'postcode',
                    'operator' => '==',
                    'value' => '78000'
                ],
                [
                    'type' => Address::class,
                    'attribute' => 'region',
                    'operator' => '==',
                    'value' => 'HD'
                ],
                [
                    'type' => Address::class,
                    'attribute' => 'region_id',
                    'operator' => '==',
                    'value' => '56'
                ],
                [
                    'type' => Address::class,
                    'attribute' => 'country_id',
                    'operator' => '==',
                    'value' => 'US'
                ]
        ],
]);

$salesRule->save();

/** @var Magento\Framework\Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);

$registry->unregister('_fixture/Magento_SalesRule_Group_Multiple_Categories');
$registry->register('_fixture/Magento_SalesRule_Group_Multiple_Categories', $salesRule);
