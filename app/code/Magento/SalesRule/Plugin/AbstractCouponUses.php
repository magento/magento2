<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Plugin;

use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\SalesRule\Model\Rule\CustomerFactory;
use Magento\SalesRule\Model\RuleFactory;

/**
 * Abstract class for plugins that are accounting the coupon uses.
 */
abstract class AbstractCouponUses
{
    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var RuleFactory
     */
    protected $ruleCustomerFactory;

    /**
     * @var Coupon
     */
    protected $coupon;

    /**
     * @var Usage
     */
    protected $couponUsage;

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
     * Updates coupon uses.
     *
     * @param Order $subject
     * @param bool $increment
     * @return Order
     */
    protected function updateCouponUses(Order $subject, bool $increment)
    {
        if (!$subject || !$subject->getAppliedRuleIds()) {
            return $subject;
        }

        // lookup rule ids
        $ruleIds = explode(',', $subject->getAppliedRuleIds());
        $ruleIds = array_unique($ruleIds);

        $ruleCustomer = null;
        $customerId = $subject->getCustomerId();

        // use each rule (and apply to customer, if applicable)
        foreach ($ruleIds as $ruleId) {
            if (!$ruleId) {
                continue;
            }
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
            }
        }

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

        return $subject;
    }
}
