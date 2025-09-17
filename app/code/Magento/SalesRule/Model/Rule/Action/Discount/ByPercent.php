<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

class ByPercent extends AbstractDiscount
{
    /**
     * Calculate discount by percent
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return Data
     */
    public function calculate($rule, $item, $qty)
    {
        $rulePercent = min(100, $rule->getDiscountAmount());
        $discountData = $this->_calculate($rule, $item, $qty, $rulePercent);

        return $discountData;
    }

    /**
     * Fix quantity depending on discount step
     *
     * @param float $qty
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return float
     */
    public function fixQuantity($qty, $rule)
    {
        $step = $rule->getDiscountStep();
        if ($step) {
            $qty = floor($qty / $step) * $step;
        }

        return $qty;
    }

    /**
     * Calculate discount by rule percent
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @param float $rulePercent
     * @return Data
     */
    protected function _calculate($rule, $item, $qty, $rulePercent)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);

        $_rulePct = $rulePercent / 100;

        $amount = ($qty * $itemPrice - $item->getDiscountAmount()) * $_rulePct;
        $baseAmount = ($qty * $baseItemPrice - $item->getBaseDiscountAmount()) * $_rulePct;
        $originalAmount = ($qty * $itemOriginalPrice - $item->getDiscountAmount()) * $_rulePct;
        $baseOriginalAmount = ($qty * $baseItemOriginalPrice - $item->getBaseDiscountAmount()) * $_rulePct;

        $discountData->setAmount(round(floatval((string) $amount), 2));
        $discountData->setBaseAmount(round(floatval((string) $baseAmount), 2));
        $discountData->setOriginalAmount(round(floatval((string) $originalAmount), 2));
        $discountData->setBaseOriginalAmount(round(floatval((string) $baseOriginalAmount), 2));

        if (!$rule->getDiscountQty() || $rule->getDiscountQty() >= $qty) {
            $discountPercent = min(100, $item->getDiscountPercent() + $rulePercent);
            $item->setDiscountPercent($discountPercent);
        }

        return $discountData;
    }
}
