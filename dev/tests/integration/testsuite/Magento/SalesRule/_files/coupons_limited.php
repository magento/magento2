<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\Rule;
use Magento\TestFramework\Helper\Bootstrap;

// phpcs:ignore Magento2.Security.IncludeFile
require 'rules.php';
// phpcs:enable

$collection = Bootstrap::getObjectManager()->create(
    Collection::class
);
$items = array_values($collection->getItems());
/** @var Rule $rule */
foreach ($items as $rule) {
    $rule->setSimpleAction('by_percent')
        ->setDiscountAmount(10)
        ->save();
}

/** @var Coupon $coupon */
$coupon = Bootstrap::getObjectManager()->create(Coupon::class);
$coupon->setRuleId($items[0]->getId())
    ->setCode('one_usage')
    ->setType(0)
    ->setUsageLimit(1)
    ->save();

$coupon = Bootstrap::getObjectManager()->create(Coupon::class);
$coupon->setRuleId($items[1]->getId())
    ->setCode('one_usage_per_customer')
    ->setType(0)
    ->setUsagePerCustomer(1)
    ->save();
