<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Coupon\Usage;

use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\SalesRule\Model\Rule\CustomerFactory;
use Magento\SalesRule\Model\RuleFactory;

/**
 * Processor to update coupon usage
 */
class Processor
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var CustomerFactory
     */
    private $ruleCustomerFactory;

    /**
     * @var CouponFactory
     */
    private $couponFactory;

    /**
     * @var Usage
     */
    private $couponUsage;

    /**
     * @param RuleFactory $ruleFactory
     * @param CustomerFactory $ruleCustomerFactory
     * @param CouponFactory $couponFactory
     * @param Usage $couponUsage
     */
    public function __construct(
        RuleFactory $ruleFactory,
        CustomerFactory $ruleCustomerFactory,
        CouponFactory $couponFactory,
        Usage $couponUsage
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->ruleCustomerFactory = $ruleCustomerFactory;
        $this->couponFactory = $couponFactory;
        $this->couponUsage = $couponUsage;
    }

    /**
     * Update coupon usage
     *
     * @param UpdateInfo $updateInfo
     */
    public function process(UpdateInfo $updateInfo): void
    {
        if (empty($updateInfo->getAppliedRuleIds())) {
            return;
        }

        $this->updateCouponUsages($updateInfo);
        $this->updateRuleUsages($updateInfo);
        $this->updateCustomerRulesUsages($updateInfo);
    }

    /**
     * Update the number of coupon usages
     *
     * @param UpdateInfo $updateInfo
     */
    public function updateCouponUsages(UpdateInfo $updateInfo): void
    {
        $coupon = $this->retrieveCoupon($updateInfo);
        if (!$coupon) {
            return;
        }

        $isIncrement = $updateInfo->isIncrement();
        if (!$updateInfo->isCouponAlreadyApplied()
            && ($updateInfo->isIncrement() || $coupon->getTimesUsed() > 0)) {
            $coupon->setTimesUsed($coupon->getTimesUsed() + ($isIncrement ? 1 : -1));
            $coupon->save();
        }
    }

    /**
     * Update the number of rule usages
     *
     * @param UpdateInfo $updateInfo
     */
    public function updateRuleUsages(UpdateInfo $updateInfo): void
    {
        $isIncrement = $updateInfo->isIncrement();
        foreach ($updateInfo->getAppliedRuleIds() as $ruleId) {
            $rule = $this->ruleFactory->create();
            $rule->load($ruleId);
            if (!$rule->getId()) {
                continue;
            }

            $rule->loadCouponCode();
            if ($isIncrement || $rule->getTimesUsed() > 0) {
                $rule->setTimesUsed($rule->getTimesUsed() + ($isIncrement ? 1 : -1));
                $rule->save();
            }
        }
    }

    /**
     * Update the number of rules usages per customer
     *
     * @param UpdateInfo $updateInfo
     */
    public function updateCustomerRulesUsages(UpdateInfo $updateInfo): void
    {
        $customerId = $updateInfo->getCustomerId();
        if (!$customerId) {
            return;
        }

        $isIncrement = $updateInfo->isIncrement();
        foreach ($updateInfo->getAppliedRuleIds() as $ruleId) {
            $this->updateCustomerRuleUsages($isIncrement, $ruleId, $customerId);
        }

        $coupon = $this->retrieveCoupon($updateInfo);
        if ($coupon) {
            $this->couponUsage->updateCustomerCouponTimesUsed($customerId, $coupon->getId(), $isIncrement);
        }
    }

    /**
     * Update the number of rule usages per customer
     *
     * @param bool $isIncrement
     * @param int $ruleId
     * @param int $customerId
     * @throws \Exception
     */
    private function updateCustomerRuleUsages(bool $isIncrement, int $ruleId, int $customerId): void
    {
        $ruleCustomer = $this->ruleCustomerFactory->create();
        $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
        if ($ruleCustomer->getId()) {
            if ($isIncrement || $ruleCustomer->getTimesUsed() > 0) {
                $ruleCustomer->setTimesUsed($ruleCustomer->getTimesUsed() + ($isIncrement ? 1 : -1));
            }
        } elseif ($isIncrement) {
            $ruleCustomer->setCustomerId($customerId)->setRuleId($ruleId)->setTimesUsed(1);
        }

        if ($ruleCustomer->hasData()) {
            $ruleCustomer->save();
        }
    }

    /**
     * Retrieve coupon from update info
     *
     * @param UpdateInfo $updateInfo
     * @return Coupon|null
     */
    private function retrieveCoupon(UpdateInfo $updateInfo): ?Coupon
    {
        if (!$updateInfo->getCouponCode()) {
            return null;
        }

        $coupon = $this->couponFactory->create();
        $coupon->loadByCode($updateInfo->getCouponCode());

        return $coupon->getId() ? $coupon : null;
    }
}
