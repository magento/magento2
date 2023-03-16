<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;

class AddSalesRuleNameToOrderObserver implements ObserverInterface
{
    /**
     * @var RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var Coupon
     */
    protected $_coupon;

    /**
     * @param RuleFactory $ruleFactory
     * @param Coupon $coupon
     */
    public function __construct(
        RuleFactory $ruleFactory,
        Coupon $coupon
    ) {
        $this->_ruleFactory = $ruleFactory;
        $this->_coupon = $coupon;
    }

    /**
     * Add coupon's rule name to order data
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getOrder();
        $couponCode = $order->getCouponCode();

        if (empty($couponCode)) {
            return $this;
        }

        $this->_coupon->loadByCode($couponCode);
        $ruleId = $this->_coupon->getRuleId();

        if (empty($ruleId)) {
            return $this;
        }

        /** @var Rule $rule */
        $rule = $this->_ruleFactory->create()->load($ruleId);
        $order->setCouponRuleName($rule->getName());

        return $this;
    }
}
