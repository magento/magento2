<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_SalesRule
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\SalesRule\Model;

class Observer
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
     * @var \Magento\SalesRule\Model\Resource\Coupon\Usage
     */
    protected $_couponUsage;

    /**
     * @var \Magento\SalesRule\Model\Resource\Report\Rule
     */
    protected $_reportRule;

    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * @var \Magento\SalesRule\Model\Resource\Rule\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\SalesRule\Model\Rule\CustomerFactory $ruleCustomerFactory
     * @param \Magento\SalesRule\Model\Coupon $coupon
     * @param \Magento\SalesRule\Model\Resource\Coupon\Usage $couponUsage
     * @param \Magento\SalesRule\Model\Resource\Report\Rule $reportRule
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\SalesRule\Model\Resource\Rule\CollectionFactory $collectionFactory
     * @param \Magento\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Model\Rule\CustomerFactory $ruleCustomerFactory,
        \Magento\SalesRule\Model\Coupon $coupon,
        \Magento\SalesRule\Model\Resource\Coupon\Usage $couponUsage,
        \Magento\SalesRule\Model\Resource\Report\Rule $reportRule,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\SalesRule\Model\Resource\Rule\CollectionFactory $collectionFactory,
        \Magento\Message\ManagerInterface $messageManager
    ) {
        $this->_ruleFactory = $ruleFactory;
        $this->_ruleCustomerFactory = $ruleCustomerFactory;
        $this->_coupon = $coupon;
        $this->_couponUsage = $couponUsage;
        $this->_reportRule = $reportRule;
        $this->_locale = $locale;
        $this->_collectionFactory = $collectionFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @param \Magento\Event\Observer $observer
     * @return $this
     */
    public function salesOrderAfterPlace($observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order || $order->getDiscountAmount() == 0) {
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
                $rule->setTimesUsed($rule->getTimesUsed() + 1);
                $rule->save();

                if ($customerId) {
                    /** @var \Magento\SalesRule\Model\Rule\Customer $ruleCustomer */
                    $ruleCustomer = $this->_ruleCustomerFactory->create();
                    $ruleCustomer->loadByCustomerRule($customerId, $ruleId);

                    if ($ruleCustomer->getId()) {
                        $ruleCustomer->setTimesUsed($ruleCustomer->getTimesUsed()+1);
                    } else {
                        $ruleCustomer->setCustomerId($customerId)
                            ->setRuleId($ruleId)
                            ->setTimesUsed(1);
                    }
                    $ruleCustomer->save();
                }
            }
        }

        $this->_coupon->load($order->getCouponCode(), 'code');
        if ($this->_coupon->getId()) {
            $this->_coupon->setTimesUsed($this->_coupon->getTimesUsed() + 1);
            $this->_coupon->save();
            if ($customerId) {
                $this->_couponUsage->updateCustomerCouponTimesUsed($customerId, $this->_coupon->getId());
            }
        }
        return $this;
    }

    /**
     * Refresh sales coupons report statistics for last day
     *
     * @param \Magento\Cron\Model\Schedule $schedule
     * @return \Magento\SalesRule\Model\Observer
     */
    public function aggregateSalesReportCouponsData($schedule)
    {
        $this->_locale->emulate(0);
        $currentDate = $this->_locale->date();
        $date = $currentDate->subHour(25);
        $this->_reportRule->aggregate($date);
        $this->_locale->revert();
        return $this;
    }

    /**
     * Check rules that contains affected attribute
     * If rules were found they will be set to inactive and notice will be add to admin session
     *
     * @param string $attributeCode
     * @return \Magento\SalesRule\Model\Observer
     */
    protected function _checkSalesRulesAvailability($attributeCode)
    {
        /* @var $collection \Magento\SalesRule\Model\Resource\Rule\Collection */
        $collection = $this->_collectionFactory->create()->addAttributeInConditionFilter($attributeCode);

        $disabledRulesCount = 0;
        foreach ($collection as $rule) {
            /* @var $rule \Magento\SalesRule\Model\Rule */
            $rule->setIsActive(0);
            /* @var $rule->getConditions() \Magento\SalesRule\Model\Rule\Condition\Combine */
            $this->_removeAttributeFromConditions($rule->getConditions(), $attributeCode);
            $this->_removeAttributeFromConditions($rule->getActions(), $attributeCode);
            $rule->save();

            $disabledRulesCount++;
        }

        if ($disabledRulesCount) {
            $this->messageManager->addWarning(__(
                '%1 Shopping Cart Price Rules based on "%2" attribute have been disabled.',
                $disabledRulesCount,
                $attributeCode
            ));
        }

        return $this;
    }

    /**
     * Remove catalog attribute condition by attribute code from rule conditions
     *
     * @param \Magento\Rule\Model\Condition\Combine $combine
     * @param string $attributeCode
     */
    protected function _removeAttributeFromConditions($combine, $attributeCode)
    {
        $conditions = $combine->getConditions();
        foreach ($conditions as $conditionId => $condition) {
            if ($condition instanceof \Magento\Rule\Model\Condition\Combine) {
                $this->_removeAttributeFromConditions($condition, $attributeCode);
            }
            if ($condition instanceof \Magento\SalesRule\Model\Rule\Condition\Product) {
                if ($condition->getAttribute() == $attributeCode) {
                    unset($conditions[$conditionId]);
                }
            }
        }
        $combine->setConditions($conditions);
    }

    /**
     * After save attribute if it is not used for promo rules already check rules for containing this attribute
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\SalesRule\Model\Observer
     */
    public function catalogAttributeSaveAfter(\Magento\Event\Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute->dataHasChangedFor('is_used_for_promo_rules') && !$attribute->getIsUsedForPromoRules()) {
            $this->_checkSalesRulesAvailability($attribute->getAttributeCode());
        }

        return $this;
    }

    /**
     * After delete attribute check rules that contains deleted attribute
     * If rules was found they will seted to inactive and added notice to admin session
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\SalesRule\Model\Observer
     */
    public function catalogAttributeDeleteAfter(\Magento\Event\Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute->getIsUsedForPromoRules()) {
            $this->_checkSalesRulesAvailability($attribute->getAttributeCode());
        }

        return $this;
    }

    /**
     * Add coupon's rule name to order data
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\SalesRule\Model\Observer
     */
    public function addSalesRuleNameToOrder($observer)
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

