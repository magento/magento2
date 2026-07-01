<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model;

use Magento\Framework\DataObjectFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Model\ResourceModel\Coupon\UsageFactory;

class ValidateCouponCode
{
    /**
     * @param CouponFactory $couponFactory
     * @param DataObjectFactory $objectFactory
     * @param UsageFactory $usageFactory
     * @param OrderEditUsageOffset $orderEditUsageOffset
     */
    public function __construct(
        private readonly CouponFactory $couponFactory,
        private readonly DataObjectFactory $objectFactory,
        private readonly UsageFactory $usageFactory,
        private readonly OrderEditUsageOffset $orderEditUsageOffset
    ) {
    }

    /**
     * Validate coupon code
     *
     * @param string[] $couponCodes
     * @param int|null $customerId
     * @param CartInterface|null $quote
     * @return string[]
     */
    public function execute(array $couponCodes, ?int $customerId = null, ?CartInterface $quote = null): array
    {
        $validCouponCodes = [];
        foreach ($couponCodes as $code) {
            if (!$code) {
                continue;
            }
            $coupon = $this->couponFactory->create()->load($code, 'code');

            if (!$this->isCouponValid($coupon, $customerId, $quote)) {
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
     * @param CartInterface|null $quote
     * @return bool
     */
    private function isCouponValid(
        CouponInterface $coupon,
        ?int $customerId = null,
        ?CartInterface $quote = null
    ): bool {
        if (!$coupon->getId()) {
            return false;
        }

        $orderEditOffset = $this->getOrderEditOffset($quote, $coupon);
        if ($this->isTotalUsageLimitReached($coupon, $orderEditOffset)) {
            return false;
        }

        return !$this->isPerCustomerUsageLimitReached($coupon, $customerId, $orderEditOffset);
    }

    /**
     * Resolve order-edit usage offset for a coupon rule.
     *
     * @param CartInterface|null $quote
     * @param CouponInterface $coupon
     * @return int
     */
    private function getOrderEditOffset(?CartInterface $quote, CouponInterface $coupon): int
    {
        if (!$coupon->getRuleId()) {
            return 0;
        }

        return $this->orderEditUsageOffset->getOffsetForRuleId((int)$coupon->getRuleId(), $quote);
    }

    /**
     * Check whether coupon total usage limit is reached.
     *
     * @param CouponInterface $coupon
     * @param int $orderEditOffset
     * @return bool
     */
    private function isTotalUsageLimitReached(CouponInterface $coupon, int $orderEditOffset): bool
    {
        return (bool)($coupon->getUsageLimit()
            && $coupon->getTimesUsed() - $orderEditOffset >= $coupon->getUsageLimit());
    }

    /**
     * Check whether coupon per-customer usage limit is reached.
     *
     * @param CouponInterface $coupon
     * @param int|null $customerId
     * @param int $orderEditOffset
     * @return bool
     */
    private function isPerCustomerUsageLimitReached(
        CouponInterface $coupon,
        ?int $customerId,
        int $orderEditOffset
    ): bool {
        if (!$customerId || !$coupon->getUsagePerCustomer()) {
            return false;
        }

        $couponUsage = $this->objectFactory->create();
        $this->usageFactory->create()->loadByCustomerCoupon(
            $couponUsage,
            $customerId,
            $coupon->getId()
        );

        return (bool)($couponUsage->getCouponId()
            && $couponUsage->getTimesUsed() - $orderEditOffset >= $coupon->getUsagePerCustomer());
    }
}
