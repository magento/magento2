<?php
/**
 * Save quote_with_coupon fixture
 *
 * The quote is not saved inside the original fixture. It is later saved inside child fixtures, but along with some
 * additional data which may break some tests.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/discount_10percent.php');
Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/quote_with_address_saved.php');
/** @var QuoteFactory $quoteFactory */
$quoteFactory = Bootstrap::getObjectManager()->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = Bootstrap::getObjectManager()->get(QuoteResource::class);
$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test_order_1', 'reserved_order_id');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$salesRuleFactory = $objectManager->get(\Magento\SalesRule\Model\RuleFactory::class);
$salesRule = $salesRuleFactory->create();
$salesRuleId = $objectManager->get(\Magento\Framework\Registry::class)
    ->registry('Magento/Checkout/_file/discount_10percent');
$salesRule->load($salesRuleId);
$couponCode = $salesRule->getPrimaryCoupon()->getCode();

$quote->setCouponCode(trim($couponCode));
$quote->collectTotals()->save();
