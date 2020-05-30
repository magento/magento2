<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/SalesRule/_files/coupons_limited.php');
Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');

$collection = Bootstrap::getObjectManager()->create(
    \Magento\SalesRule\Model\ResourceModel\Rule\Collection::class
);
$items = array_values($collection->getItems());

/** @var Order $order */
$order = Bootstrap::getObjectManager()->create(Order::class);

$order->loadByIncrementId('100000001')
    ->setCouponCode('one_usage')
    ->setAppliedRuleIds("{$items[0]->getId()}")
    ->setCreatedAt('2014-10-25 10:10:10')
    ->setCustomerId(1)
    ->save();
