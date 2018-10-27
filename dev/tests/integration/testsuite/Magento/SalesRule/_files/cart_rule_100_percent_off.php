<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

/** @var \Magento\SalesRule\Model\Rule $salesRule */
$salesRule = $objectManager->create(\Magento\SalesRule\Model\Rule::class);
$salesRule->setData(
    [
        'name' => '100% Off for all orders',
        'is_active' => 1,
        'customer_group_ids' => [\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID],
        'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON,
        'conditions' => [],
        'simple_action' => 'by_percent',
        'discount_amount' => 100,
        'discount_step' => 0,
        'stop_rules_processing' => 1,
        'website_ids' => [
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getWebsite()->getId()
        ],
        'discount_qty' => 0,
        'apply_to_shipping' => 1,
        'simple_free_shipping' => 1,
    ]
);
$salesRule->save();
$registry->unregister('Magento/SalesRule/_files/cart_rule_100_percent_off');
$registry->register('Magento/SalesRule/_files/cart_rule_100_percent_off', $salesRule->getRuleId());
