<?php
/**
 * SalesRule 10% discount coupon
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\SalesRule\Model\Rule $salesRule */
$salesRule = $objectManager->create(\Magento\SalesRule\Model\Rule::class);

$data = [
    'name' => 'Test Coupon for General',
    'is_active' => true,
    'store_labels' => [0 => 'Test Coupon for General'],
    'website_ids' => [
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getStore()->getWebsiteId()
    ],
    'customer_group_ids' => [1],
    'coupon_type' => \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC,
    'coupon_code' => '2?ds5!2d',
    'simple_action' => \Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION,
    'discount_amount' => 10,
    'discount_step' => 1
];

$salesRule->loadPost($data)->setUseAutoGeneration(false)->save();
$objectManager->get(
    \Magento\Framework\Registry::class
)->unregister('Magento/Checkout/_file/discount_10percent_generalusers');
$objectManager->get(\Magento\Framework\Registry::class)
    ->register('Magento/Checkout/_file/discount_10percent_generalusers', $salesRule->getRuleId());
