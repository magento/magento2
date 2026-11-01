<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\OfflineShipping\Model\SalesRule;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Validator;
use \Magento\SalesRule\Model\Rule as SalesRule;

/**
 * @api
 * @since 100.0.2
 */
class Calculator extends Validator
{
    /**
     * Quote item free shipping ability check
     * This process not affect information about applied rules, coupon code etc.
     * This information will be added during discount amounts processing
     *
     * @param AbstractItem $item
     *
     * @return \Magento\OfflineShipping\Model\SalesRule\Calculator
     *
     * @throws \Zend_Db_Select_Exception
     */
    public function processFreeShipping(AbstractItem $item)
    {
        $address = $item->getAddress();
        $this->resetFreeShipping($item);

        /* @var $rule SalesRule */
        foreach ($this->getRules($address) as $rule) {
            if (!$this->canApplyRuleToItem($rule, $address, $item)) {
                continue;
            }

            $this->applyFreeShippingRule($rule, $address, $item);

            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }

        return $this;
    }

    /**
     * Validates rule for item
     *
     * @param SalesRule $rule
     * @param Address $address
     * @param AbstractItem $item
     *
     * @return bool
     */
    private function canApplyRuleToItem(SalesRule $rule, Address $address, AbstractItem $item): bool
    {
        if (!$this->validatorUtility->canProcessRule($rule, $address)) {
            return false;
        }

        return (bool) $rule->getActions()->validate($item);
    }

    /**
     * Apply free shipping rule for cart item
     *
     * @param SalesRule $rule
     * @param Address $address
     * @param AbstractItem $item
     *
     * @return void
     */
    private function applyFreeShippingRule(SalesRule $rule, Address $address, AbstractItem $item): void
    {
        $type = (int) $rule->getSimpleFreeShipping();

        if ($type === Rule::FREE_SHIPPING_ITEM) {
            $this->applyItemFreeShipping($rule, $item);
            return;
        }

        if ($type === Rule::FREE_SHIPPING_ADDRESS) {
            $address->setFreeShipping(true);
        }
    }

    /**
     * Free shipping can be applied to parent or child items
     *
     * @param SalesRule $rule
     * @param AbstractItem $item
     *
     * @return void
     */
    private function applyItemFreeShipping(SalesRule $rule, AbstractItem $item): void
    {
        $method = $item->getAddress()->getShippingMethod();
        $item->setFreeShipping($rule->getDiscountQty() ? $rule->getDiscountQty() : true);
        $item->setFreeShippingMethod($method);

        if ($item->getHasChildren() && $item->isShipSeparately()) {
            foreach ($item->getChildren() as $child) {
                $child->setFreeShipping($rule->getDiscountQty() ? $rule->getDiscountQty() : true);
                $child->setFreeShippingMethod($method);
            }
        }
    }

    /**
     * Reset free shipping for item
     *
     * @param AbstractItem $item
     *
     * @return void
     */
    private function resetFreeShipping(AbstractItem $item): void
    {
        $item->setFreeShipping(false);
        if ($item->getHasChildren() && $item->isShipSeparately()) {
            foreach ($item->getChildren() as $child) {
                $child->setFreeShipping(false);
            }
        }
    }
}
