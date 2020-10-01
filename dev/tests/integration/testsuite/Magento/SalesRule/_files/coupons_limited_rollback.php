<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\SalesRule\Model\Coupon;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$couponCodes = [
    'one_usage',
    'one_usage_per_customer',
];

/** @var Coupon $coupon */
$coupon = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(Coupon::class);

foreach ($couponCodes as $couponCode) {
    $coupon->loadByCode($couponCode);
    $coupon->delete();
}

Resolver::getInstance()->requireDataFixture('Magento/SalesRule/_files/rules_rollback.php');
