<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SalesOrderAfterCancelObserver implements ObserverInterface
{
    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $_ruleCustomerFactory;

    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $_coupon;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\Usage
     */
    protected $_couponUsage;

    /**
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\SalesRule\Model\Rule\CustomerFactory $ruleCustomerFactory
     * @param \Magento\SalesRule\Model\Coupon $coupon
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\Usage $couponUsage
     */
    public function __construct(
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Model\Rule\CustomerFactory $ruleCustomerFactory,
        \Magento\SalesRule\Model\Coupon $coupon,
        \Magento\SalesRule\Model\ResourceModel\Coupon\Usage $couponUsage
    ) {
        $this->_ruleFactory = $ruleFactory;
        $this->_ruleCustomerFactory = $ruleCustomerFactory;
        $this->_coupon = $coupon;
        $this->_couponUsage = $couponUsage;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order || !$order->getAppliedRuleIds()) {
            return $this;
        }

        // lookup rule ids
        $ruleIds = explode(',', $order->getAppliedRuleIds());
        $ruleIds = array_unique($ruleIds);

        $ruleCustomer = null;
        $customerId = $order->getCustomerId();

        // use each rule (and apply to customer, if applicable)
        foreach ($ruleIds as $ruleId) {
            if (!$ruleId) {
                continue;
            }
            /** @var \Magento\SalesRule\Model\Rule $rule */
            $rule = $this->_ruleFactory->create();
            $rule->load($ruleId);
            if ($rule->getId()) {
                $rule->loadCouponCode();
                if ($rule->getTimesUsed() > 0) {
                    $rule->setTimesUsed($rule->getTimesUsed() - 1);
                    $rule->save();
                }

                if ($customerId) {
                    /** @var \Magento\SalesRule\Model\Rule\Customer $ruleCustomer */
                    $ruleCustomer = $this->_ruleCustomerFactory->create();
                    $ruleCustomer->loadByCustomerRule($customerId, $ruleId);

                    if ($ruleCustomer->getId()) {
                        if ($ruleCustomer->getTimesUsed() > 0) {
                            $ruleCustomer->setTimesUsed($ruleCustomer->getTimesUsed() - 1);
                        }
                    } else {
                        $ruleCustomer->setCustomerId($customerId)->setRuleId($ruleId)->setTimesUsed(0);
                    }
                    $ruleCustomer->save();
                }
            }
        }

        $this->_coupon->load($order->getCouponCode(), 'code');
        if ($this->_coupon->getId()) {
            if ($this->_coupon->getTimesUsed() > 0) {
                $this->_coupon->setTimesUsed($this->_coupon->getTimesUsed() - 1);
                $this->_coupon->save();
            }
            if ($customerId) {
                $this->_couponUsage->updateCustomerCouponTimesUsed($customerId, $this->_coupon->getId(), false);
            }
        }

        return $this;
    }
}
