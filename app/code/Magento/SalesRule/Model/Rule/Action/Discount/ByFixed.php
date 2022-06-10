<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

class ByFixed extends AbstractDiscount
{
    /**
     * Calculate fixed amount discount
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        $baseDiscountAmount = (float) $rule->getDiscountAmount();
        $discountAmount = $this->priceCurrency->convert($baseDiscountAmount, $item->getQuote()->getStore());
        $itemDiscountAmount = $item->getDiscountAmount();
        $itemBaseDiscountAmount = $item->getBaseDiscountAmount();
        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);

        $discountAmountMin = min(($itemPrice * $qty) - $itemDiscountAmount, $discountAmount * $qty);
        $baseDiscountAmountMin = min(($baseItemPrice * $qty) - $itemBaseDiscountAmount, $baseDiscountAmount * $qty);

        $discountData->setAmount($discountAmountMin);
        $discountData->setBaseAmount($baseDiscountAmountMin);

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
}
