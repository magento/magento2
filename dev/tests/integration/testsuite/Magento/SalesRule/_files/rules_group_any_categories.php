<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\SalesRule\Model\Rule $rule */
$salesRule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\SalesRule\Model\Rule');
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
                'Magento\Store\Model\StoreManagerInterface'
            )->getWebsite()->getId()
        ]
    ]
);

$salesRule->getConditions()->loadArray([
    'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Combine',
    'attribute' => null,
    'operator' => null,
    'value' => '1',
    'is_value_processed' => null,
    'aggregator' => 'all',
    'conditions' =>
        [
                [
                    'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product\\Found',
                    'attribute' => null,
                    'operator' => null,
                    'value' => '1',
                    'is_value_processed' => null,
                    'aggregator' => 'any',
                    'conditions' =>
                        [
                                [
                                    'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                                    'attribute' => 'category_ids',
                                    'operator' => '==',
                                    'value' => '2',
                                    'is_value_processed' => false,
                                ],
                                [
                                    'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                                    'attribute' => 'category_ids',
                                    'operator' => '==',
                                    'value' => '3',
                                    'is_value_processed' => false,
                                ],
                        ],
                ],
                [
                    'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product\\Found',
                    'attribute' => null,
                    'operator' => null,
                    'value' => '1',
                    'is_value_processed' => null,
                    'aggregator' => 'all',
                    'conditions' =>
                        [
                            [
                                'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                                'attribute' => 'category_ids',
                                'operator' => '==',
                                'value' => '3',
                                'is_value_processed' => false,
                            ],
                            [
                                'type' => 'Magento\\SalesRule\\Model\\Rule\\Condition\\Product',
                                'attribute' => 'category_ids',
                                'operator' => '==',
                                'value' => '4',
                                'is_value_processed' => false,
                            ],
                        ],
                ],

        ],
]);

$salesRule->save();

/** @var Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');

$registry->unregister('_fixture/Magento_SalesRule_Group_Multiple_Categories');
$registry->register('_fixture/Magento_SalesRule_Group_Multiple_Categories', $salesRule);
