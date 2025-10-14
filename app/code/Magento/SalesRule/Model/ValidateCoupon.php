<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model;

use Magento\Framework\DataObjectFactory;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Model\ResourceModel\Coupon\UsageFactory;

class ValidateCoupon
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
     * Validate coupon rule
     *
     * @param Rule $rule
     * @param Address $address
     * @param string|null $couponCode
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(Rule $rule, Address $address, ?string $couponCode = null): bool
    {
        if ($rule->getCouponType() == Rule::COUPON_TYPE_NO_COUPON) {
            return true;
        }

        if (!$couponCode) {
            return false;
        }

        $coupon = $this->couponFactory->create()->load($couponCode, 'code');
        if (!$coupon->getId()) {
            return false;
        }

        // check entire usage limit
        if ($coupon->getUsageLimit() && $coupon->getTimesUsed() >= $coupon->getUsageLimit()) {
            $rule->setIsValidForAddress($address, false);
            return false;
        }
        // check per customer usage limit
        $customerId = $address->getQuote()->getCustomerId();
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
            $rule->setIsValidForAddress($address, false);
            return false;
        }
        return true;
    }
}
