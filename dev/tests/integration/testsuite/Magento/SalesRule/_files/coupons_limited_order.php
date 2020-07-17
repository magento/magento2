<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Quote\Model\Quote;
use Magento\TestFramework\Helper\Bootstrap;

// phpcs:disable Magento2.Security.IncludeFile
require 'coupons_limited.php';
require __DIR__ . '/../../../Magento/Sales/_files/quote_with_customer.php';
// phpcs:enable

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
