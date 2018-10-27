<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\SalesRule\Model\Coupon;

require 'rules.php';

$collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\SalesRule\Model\ResourceModel\Rule\Collection::class
);
$items = array_values($collection->getItems());

/** @var Coupon $coupon */
$coupon = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(Coupon::class);
$coupon->setRuleId($items[0]->getId())
    ->setCode('one_usage')
    ->setType(0)
    ->setUsageLimit(1)
    ->save();

$coupon = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(Coupon::class);
$coupon->setRuleId($items[1]->getId())
    ->setCode('one_usage_per_customer')
    ->setType(0)
    ->setUsagePerCustomer(1)
    ->save();
