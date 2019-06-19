<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();


$coupon = $objectManager->create(Coupon::class);
$coupon->loadByCode('BY_FIXED_DISCOUNT_15');
if ($coupon->getCouponId()) {
    /** @var CouponRepositoryInterface $couponRepository */
    $couponRepository = $objectManager->get(CouponRepositoryInterface::class);
    $couponRepository->deleteById($coupon->getCouponId());
}
