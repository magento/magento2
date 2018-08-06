<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\SalesRule\Model\Coupon;

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

require 'rules_rollback.php';
