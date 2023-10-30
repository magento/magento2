<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Coupon\Usage;

use Magento\SalesRule\Model\Coupon;
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
     * @var Coupon
     */
    private $coupon;

    /**
     * @var Usage
     */
    private $couponUsage;

    /**
     * @param RuleFactory $ruleFactory
     * @param CustomerFactory $ruleCustomerFactory
     * @param Coupon $coupon
     * @param Usage $couponUsage
     */
    public function __construct(
        RuleFactory $ruleFactory,
        CustomerFactory $ruleCustomerFactory,
        Coupon $coupon,
        Usage $couponUsage
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->ruleCustomerFactory = $ruleCustomerFactory;
        $this->coupon = $coupon;
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

        if (!empty($updateInfo->getCouponCode())) {
            $this->updateCouponUsages($updateInfo);
        }
        $isIncrement = $updateInfo->isIncrement();
        $customerId = $updateInfo->getCustomerId();
        // use each rule (and apply to customer, if applicable)
        foreach (array_unique($updateInfo->getAppliedRuleIds()) as $ruleId) {
            if (!(int)$ruleId) {
                continue;
            }
            $this->updateRuleUsages($isIncrement, (int)$ruleId);
            if ($customerId) {
                $this->updateCustomerRuleUsages($isIncrement, (int)$ruleId, $customerId);
            }
        }
    }

    /**
     * Update the number of coupon usages
     *
     * @param UpdateInfo $updateInfo
     */
    private function updateCouponUsages(UpdateInfo $updateInfo): void
    {
        $isIncrement = $updateInfo->isIncrement();
        $this->coupon->load($updateInfo->getCouponCode(), 'code');
        if ($this->coupon->getId()) {
            if (!$updateInfo->isCouponAlreadyApplied()
                && ($updateInfo->isIncrement() || $this->coupon->getTimesUsed() > 0)) {
                $this->coupon->setTimesUsed($this->coupon->getTimesUsed() + ($isIncrement ? 1 : -1));
                $this->coupon->save();
            }
            if ($updateInfo->getCustomerId()) {
                $this->couponUsage->updateCustomerCouponTimesUsed(
                    $updateInfo->getCustomerId(),
                    $this->coupon->getId(),
                    $isIncrement
                );
            }
        }
    }

    /**
     * Update the number of rule usages
     *
     * @param bool $isIncrement
     * @param int $ruleId
     */
    private function updateRuleUsages(bool $isIncrement, int $ruleId): void
    {
        $rule = $this->ruleFactory->create();
        $rule->load($ruleId);
        if ($rule->getId()) {
            $rule->loadCouponCode();
            if ($isIncrement || $rule->getTimesUsed() > 0) {
                $rule->setTimesUsed($rule->getTimesUsed() + ($isIncrement ? 1 : -1));
                $rule->save();
            }
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
}
