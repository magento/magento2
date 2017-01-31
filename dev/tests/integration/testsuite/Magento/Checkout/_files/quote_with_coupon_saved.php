<?php
/**
 * Save quote_with_coupon fixture
 *
 * The quote is not saved inside the original fixture. It is later saved inside child fixtures, but along with some
 * additional data which may break some tests.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/../../Checkout/_files/discount_10percent.php';

require 'quote_with_address_saved.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$salesRuleFactory = $objectManager->get('Magento\SalesRule\Model\RuleFactory');
$salesRule = $salesRuleFactory->create();
$salesRuleId = $objectManager->get('Magento\Framework\Registry')
    ->registry('Magento/Checkout/_file/discount_10percent');
$salesRule->load($salesRuleId);
$couponCode = $salesRule->getPrimaryCoupon()->getCode();

$quote->setCouponCode(trim($couponCode));
$quote->collectTotals()->save();
