<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var Magento\Framework\Registry $registry */
$registry = $objectManager->get('Magento\Framework\Registry');

/** @var \Magento\SalesRule\Model\Rule $salesRule */
$salesRule = $objectManager->create('Magento\SalesRule\Model\Rule');
$salesRule->setData(
    [
        'name' => '40% Off on Large Orders',
        'is_active' => 1,
        'customer_group_ids' => [\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON,
        'conditions' => [
            [
                'type' => 'Magento\SalesRule\Model\Rule\Condition\Address',
                'attribute' => 'base_subtotal',
                'operator' => '>',
                'value' => 800
            ]
        ],
        'simple_action' => 'by_percent',
        'discount_amount' => 40,
        'discount_step' => 0,
        'stop_rules_processing' => 1,
        'website_ids' => [
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Store\Model\StoreManagerInterface'
            )->getWebsite()->getId()
        ]
    ]
);
$salesRule->save();
$registry->unregister('Magento/SalesRule/_files/cart_rule_40_percent_off');
$registry->register('Magento/SalesRule/_files/cart_rule_40_percent_off', $salesRule->getRuleId());
