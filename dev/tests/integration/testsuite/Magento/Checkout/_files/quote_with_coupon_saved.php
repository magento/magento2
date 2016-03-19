<?php
/**
 * Save quote_with_coupon fixture
 *
 * The quote is not saved inside the original fixture. It is later saved inside child fixtures, but along with some
 * additional data which may break some tests.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/../../Checkout/_files/discount_10percent.php';

require 'quote_with_address_saved.php';

$salesRule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\SalesRule\Model\Rule');
$salesRule->load('Test Coupon', 'name');
$couponCode = $salesRule->getPrimaryCoupon()->getCode();

$quote->setCouponCode(trim($couponCode));
$quote->collectTotals()->save();
