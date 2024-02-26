<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Coupon\Usage;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\SalesRule\Api\CouponRepositoryInterface;
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
     * @param RuleFactory $ruleFactory
     * @param CustomerFactory $ruleCustomerFactory
     * @param Usage $couponUsage
     * @param CouponRepositoryInterface $couponRepository
     * @param SearchCriteriaBuilder $criteriaBuilder
     */
    public function __construct(
        private readonly RuleFactory $ruleFactory,
        private readonly CustomerFactory $ruleCustomerFactory,
        private readonly Usage $couponUsage,
        private readonly CouponRepositoryInterface $couponRepository,
        private readonly SearchCriteriaBuilder $criteriaBuilder
    ) {
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
        $isIncrement = $updateInfo->isIncrement();
        $coupons = $this->retrieveCoupons($updateInfo);

        if ($updateInfo->isCouponAlreadyApplied()) {
            return;
        }

        foreach ($coupons as $coupon) {
            if ($updateInfo->isIncrement() || $coupon->getTimesUsed() > 0) {
                $coupon->setTimesUsed($coupon->getTimesUsed() + ($isIncrement ? 1 : -1));
                $coupon->save();
            }
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
            $rule = $this->ruleFactory->create();
            $rule->load($ruleId);
            if (!$rule->getId()) {
                continue;
            }
            $this->updateCustomerRuleUsages($isIncrement, $ruleId, $customerId);
        }

        $coupons = $this->retrieveCoupons($updateInfo);
        foreach ($coupons as $coupon) {
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
     * @return Coupon[]
     */
    private function retrieveCoupons(UpdateInfo $updateInfo): array
    {
        if (!$updateInfo->getCouponCode() && empty($updateInfo->getCouponCodes())) {
            return [];
        }

        $coupons = $updateInfo->getCouponCodes();
        if ($updateInfo->getCouponCode() && !in_array($updateInfo->getCouponCode(), $coupons)) {
            array_unshift($coupons, $updateInfo->getCouponCode());
        }

        return $this->couponRepository->getList(
            $this->criteriaBuilder->addFilter(
                'code',
                $coupons,
                'in'
            )->create()
        )->getItems();
    }
}
