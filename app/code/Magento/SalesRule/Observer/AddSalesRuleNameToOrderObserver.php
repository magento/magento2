<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddSalesRuleNameToOrderObserver implements ObserverInterface
{
    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $_coupon;

    /**
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\SalesRule\Model\Coupon $coupon
     */
    public function __construct(
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Model\Coupon $coupon
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

        /** @var \Magento\SalesRule\Model\Rule $rule */
        $rule = $this->_ruleFactory->create()->load($ruleId);
        $order->setCouponRuleName($rule->getName());

        return $this;
    }
}
