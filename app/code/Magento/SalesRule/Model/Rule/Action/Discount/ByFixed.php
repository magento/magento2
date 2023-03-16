<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data as DiscountData;

class ByFixed extends AbstractDiscount
{
    /**
     * Calculate fixed amount discount
     *
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     * @return DiscountData
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var DiscountData $discountData */
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
     * @param Rule $rule
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
