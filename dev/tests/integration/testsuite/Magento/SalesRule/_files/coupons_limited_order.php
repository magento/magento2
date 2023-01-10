<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/SalesRule/_files/coupons_limited.php');
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/quote_with_customer.php');

$collection = Bootstrap::getObjectManager()->create(
    \Magento\SalesRule\Model\ResourceModel\Rule\Collection::class
);
$items = array_values($collection->getItems());

/** @var Quote $quote */
$quote = Bootstrap::getObjectManager()->create(Quote::class);
$quote->load('test01', 'reserved_order_id');
$quote->getShippingAddress()
    ->setShippingMethod('flatrate_flatrate')
    ->setShippingDescription('Flat Rate - Fixed')
    ->setCollectShippingRates(true)
    ->collectShippingRates()
    ->save();

$quote->setCouponCode('one_usage')
    ->setAppliedRuleIds("{$items[0]->getId()}")
    ->save();
