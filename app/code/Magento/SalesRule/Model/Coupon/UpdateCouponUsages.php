<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model\Coupon;

use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\SalesRule\Model\Rule\CustomerFactory;
use Magento\SalesRule\Model\RuleFactory;
use Magento\SalesRule\Model\ResourceModel\Rule\DeleteCustomerUsage;
use Magento\Framework\App\ObjectManager;

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
     * @var DeleteCustomerUsage
     */
    private $deleteCustomerRuleUsage;

    /**
     * @param RuleFactory $ruleFactory
     * @param CustomerFactory $ruleCustomerFactory
     * @param Coupon $coupon
     * @param Usage $couponUsage
     * @param DeleteCustomerUsage $deleteCustomerRuleUsage
     */
    public function __construct(
        RuleFactory $ruleFactory,
        CustomerFactory $ruleCustomerFactory,
        Coupon $coupon,
        Usage $couponUsage,
        DeleteCustomerUsage $deleteCustomerRuleUsage = null
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->ruleCustomerFactory = $ruleCustomerFactory;
        $this->coupon = $coupon;
        $this->couponUsage = $couponUsage;
        $this->deleteCustomerRuleUsage = $deleteCustomerRuleUsage ? :
            ObjectManager::getInstance()->get(DeleteCustomerUsage::class);
    }

    /**
     * Executes the current command.
     *
     * @param Order $subject
     * @param bool $increment
     * @return Order
     */
    public function execute(Order $subject, bool $increment)
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
    private function updateCustomerRuleUsages(bool $increment, int $ruleId, int $customerId)
    {
        /** @var \Magento\SalesRule\Model\Rule\Customer $ruleCustomer */
        $ruleCustomer = $this->ruleCustomerFactory->create();
        $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
        if ($ruleCustomer->getId()) {
            if ($increment || $ruleCustomer->getTimesUsed() > 0) {
                $ruleCustomer->setTimesUsed($ruleCustomer->getTimesUsed() + ($increment ? 1 : -1));
                /** Delete the rules from salesrule_customer table when time used updated to 0 */
                $updatedTimeUsed = $ruleCustomer->getTimesUsed();
                if ($updatedTimeUsed === 0) {
                    $this->deleteCustomerRuleUsage->execute($ruleId, $customerId, $updatedTimeUsed);
                }
            }
        } elseif ($increment) {
            $ruleCustomer->setCustomerId($customerId)->setRuleId($ruleId)->setTimesUsed(1);
        }
        $ruleCustomer->save();
    }

    /**
     * Update the number of coupon usages.
     *
     * @param Order $subject
     * @param bool $increment
     * @param int $customerId
     */
    private function updateCouponUsages(Order $subject, bool $increment, int $customerId)
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
