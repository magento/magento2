<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\SalesRule\Model\Rule $rule */
$salesRule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\SalesRule\Model\Rule::class);
$salesRule->setData(
    [
        'name' => '50% Off on Large Orders',
        'is_active' => 1,
        'customer_group_ids' => [\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON,
        'simple_action' => 'by_percent',
        'discount_amount' => 50,
        'discount_step' => 0,
        'stop_rules_processing' => 1,
        'website_ids' => [
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getWebsite()->getId()
        ]
    ]
);

$salesRule->getConditions()->loadArray([
    'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
    'attribute' => null,
    'operator' => null,
    'value' => '1',
    'is_value_processed' => null,
    'aggregator' => 'any',
    'conditions' =>
        [
                [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                    'attribute' => null,
                    'operator' => null,
                    'value' => '1',
                    'is_value_processed' => null,
                    'aggregator' => 'any',
                    'conditions' =>
                        [
                                [
                                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                                    'attribute' => 'category_ids',
                                    'operator' => '==',
                                    'value' => '2',
                                    'is_value_processed' => false,
                                ],
                                [
                                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                                    'attribute' => 'attribute_set_id',
                                    'operator' => '==',
                                    'value' => '4',
                                    'is_value_processed' => false,
                                ],
                        ],
                ],
                [
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Found::class,
                    'attribute' => null,
                    'operator' => null,
                    'value' => '1',
                    'is_value_processed' => null,
                    'aggregator' => 'any',
                    'conditions' =>
                        [
                            [
                                'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                                'attribute' => 'quote_item_price',
                                'operator' => '==',
                                'value' => '80',
                                'is_value_processed' => false,
                            ],
                            [
                                'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                                'attribute' => 'category_ids',
                                'operator' => '==',
                                'value' => '3',
                                'is_value_processed' => false,
                            ],
                        ],
                ],

        ],
]);

$salesRule->save();

/** @var Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('_fixture/Magento_SalesRule_Group_Multiple_Categories');
$registry->register('_fixture/Magento_SalesRule_Group_Multiple_Categories', $salesRule);
