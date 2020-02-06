<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Coupon;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\SalesRule\Model\Rule\CustomerFactory;
use Magento\SalesRule\Model\RuleFactory;

/**
 * Updates the coupon usages.
 */
class UpdateCouponUsages
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var RuleFactory
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
     * Executes the current command.
     *
     * @param OrderInterface $subject
     * @param bool $increment
     * @return OrderInterface
     */
    public function execute(OrderInterface $subject, bool $increment): OrderInterface
    {
        if (!$subject || !$subject->getAppliedRuleIds()) {
            return $subject;
        }
        // lookup rule ids
        $ruleIds = explode(',', $subject->getAppliedRuleIds());
        $ruleIds = array_unique($ruleIds);
        $customerId = (int)$subject->getCustomerId();
        // use each rule (and apply to customer, if applicable)
        foreach ($ruleIds as $ruleId) {
            if (!$ruleId) {
                continue;
            }
            $this->updateRuleUsages($increment, (int)$ruleId, $customerId);
        }
        $this->updateCouponUsages($subject, $increment, $customerId);

        return $subject;
    }

    /**
     * Update the number of rule usages.
     *
     * @param bool $increment
     * @param int $ruleId
     * @param int $customerId
     */
    private function updateRuleUsages(bool $increment, int $ruleId, int $customerId)
    {
        /** @var \Magento\SalesRule\Model\Rule $rule */
        $rule = $this->ruleFactory->create();
        $rule->load($ruleId);
        if ($rule->getId()) {
            $rule->loadCouponCode();
            if ($increment || $rule->getTimesUsed() > 0) {
                $rule->setTimesUsed($rule->getTimesUsed() + ($increment ? 1 : -1));
                $rule->save();
            }
            if ($customerId) {
                $this->updateCustomerRuleUsages($increment, $ruleId, $customerId);
            }
        }
    }

    /**
     * Update the number of rule usages per customer.
     *
     * @param bool $increment
     * @param int $ruleId
     * @param int $customerId
     */
    private function updateCustomerRuleUsages(bool $increment, int $ruleId, int $customerId): void
    {
        /** @var \Magento\SalesRule\Model\Rule\Customer $ruleCustomer */
        $ruleCustomer = $this->ruleCustomerFactory->create();
        $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
        if ($ruleCustomer->getId()) {
            if ($increment || $ruleCustomer->getTimesUsed() > 0) {
                $ruleCustomer->setTimesUsed($ruleCustomer->getTimesUsed() + ($increment ? 1 : -1));
            }
        } elseif ($increment) {
            $ruleCustomer->setCustomerId($customerId)->setRuleId($ruleId)->setTimesUsed(1);
        }
        $ruleCustomer->save();
    }

    /**
     * Update the number of coupon usages.
     *
     * @param OrderInterface $subject
     * @param bool $increment
     * @param int $customerId
     */
    private function updateCouponUsages(OrderInterface $subject, bool $increment, int $customerId): void
    {
        $this->coupon->load($subject->getCouponCode(), 'code');
        if ($this->coupon->getId()) {
            if ($increment || $this->coupon->getTimesUsed() > 0) {
                $this->coupon->setTimesUsed($this->coupon->getTimesUsed() + ($increment ? 1 : -1));
                $this->coupon->save();
            }
            if ($customerId) {
                $this->couponUsage->updateCustomerCouponTimesUsed($customerId, $this->coupon->getId(), $increment);
            }
        }
    }
}
