<?php
/**
 * SalesRule 10% discount coupon
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\SalesRule\Model\RuleFactory $salesRule */
$salesRuleFactory = $objectManager->get(\Magento\SalesRule\Model\RuleFactory::class);

/** @var \Magento\SalesRule\Model\Rule $salesRule */
$salesRule = $salesRuleFactory->create();

$data = [
    'name' => 'Test Coupon',
    'is_active' => true,
    'website_ids' => [
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getStore()->getWebsiteId()
    ],
    'customer_group_ids' => [\Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID],
    'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC,
    'coupon_code' => uniqid(),
    'simple_action' => \Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION,
    'discount_amount' => 10,
    'discount_step' => 1
];

$salesRule->loadPost($data)->setUseAutoGeneration(false)->save();
$objectManager->get(\Magento\Framework\Registry::class)->unregister('Magento/Checkout/_file/discount_10percent');
$objectManager->get(\Magento\Framework\Registry::class)
    ->register('Magento/Checkout/_file/discount_10percent', $salesRule->getRuleId());
