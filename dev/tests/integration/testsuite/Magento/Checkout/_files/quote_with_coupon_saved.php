<?php
/**
 * Save quote_with_coupon fixture
 *
 * The quote is not saved inside the original fixture. It is later saved inside child fixtures, but along with some
 * additional data which may break some tests.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
require __DIR__ . '/../../Checkout/_files/discount_10percent.php';

require 'quote_with_address_saved.php';

$salesRule = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\SalesRule\Model\Rule');
$salesRule->load('Test Coupon', 'name');
$couponCode = $salesRule->getCouponCode();

$quote->setCouponCode(trim($couponCode));
$quote->collectTotals()->save();
