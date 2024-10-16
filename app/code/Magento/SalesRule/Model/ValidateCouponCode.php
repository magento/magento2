<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model;

use Magento\Framework\DataObjectFactory;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Model\ResourceModel\Coupon\UsageFactory;

class ValidateCouponCode
{
    /**
     * @param CouponFactory $couponFactory
     * @param DataObjectFactory $objectFactory
     * @param UsageFactory $usageFactory
     */
    public function __construct(
        private readonly CouponFactory $couponFactory,
        private readonly DataObjectFactory $objectFactory,
        private readonly UsageFactory $usageFactory
    ) {
    }

    /**
     * Validate coupon code
     *
     * @param string[] $couponCodes
     * @param int|null $customerId
     * @return string[]
     */
    public function execute(array $couponCodes, ?int $customerId = null): array
    {
        $validCouponCodes = [];
        foreach ($couponCodes as $code) {
            if (!$code) {
                continue;
            }
            $coupon = $this->couponFactory->create()->load($code, 'code');

            if (!$this->isCouponValid($coupon, $customerId)) {
                continue;
            }
            if (isset($validCouponCodes[$coupon->getRuleId()])) {
                continue;
            }
            $validCouponCodes[$coupon->getRuleId()] = $coupon->getCode();
        }
        return $validCouponCodes;
    }

    /**
     * Validate coupon object
     *
     * @param CouponInterface $coupon
     * @param int|null $customerId
     * @return bool
     */
    private function isCouponValid(CouponInterface $coupon, ?int $customerId = null): bool
    {
        if (!$coupon->getId()) {
            return false;
        }

        if ($coupon->getUsageLimit() && $coupon->getTimesUsed() >= $coupon->getUsageLimit()) {
            return false;
        }

        if (!$customerId || !$coupon->getUsagePerCustomer()) {
            return true;
        }

        $couponUsage = $this->objectFactory->create();
        $this->usageFactory->create()->loadByCustomerCoupon(
            $couponUsage,
            $customerId,
            $coupon->getId()
        );
        if ($couponUsage->getCouponId() &&
            $couponUsage->getTimesUsed() >= $coupon->getUsagePerCustomer()
        ) {
            return false;
        }
        return true;
    }
}
