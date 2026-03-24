<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Rule\Action\Discount;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;

class BuyXGetY extends AbstractDiscount
{
    /**
     * Calculate discount data for BuyXGetY action.
     *
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     * @return Data
     */
    public function calculate($rule, $item, $qty): Data
    {
        $discountData = $this->discountFactory->create();

        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);

        // Use effective per-item price after previously applied discounts
        $itemQtyTotal = $item->getQty();
        $perItemPrevDiscount = (float) $item->getDiscountAmount() / $itemQtyTotal;
        $perItemBasePrevDiscount = (float) $item->getBaseDiscountAmount() / $itemQtyTotal;

        $effectiveItemPrice = max(0, $itemPrice - $perItemPrevDiscount);
        $effectiveBaseItemPrice = max(0, $baseItemPrice - $perItemBasePrevDiscount);
        $effectiveItemOriginalPrice = max(0, $itemOriginalPrice - $perItemPrevDiscount);
        $effectiveBaseItemOriginalPrice = max(0, $baseItemOriginalPrice - $perItemBasePrevDiscount);

        $x = $rule->getDiscountStep();
        $y = $rule->getDiscountAmount();
        if (!$x || $y > $x) {
            return $discountData;
        }
        $buyAndDiscountQty = $x + $y;

        $fullRuleQtyPeriod = floor($qty / $buyAndDiscountQty);
        $freeQty = $qty - $fullRuleQtyPeriod * $buyAndDiscountQty;

        $discountQty = $fullRuleQtyPeriod * $y;
        if ($freeQty > $x) {
            $discountQty += $freeQty - $x;
        }

        $discountData->setAmount($discountQty * $effectiveItemPrice);
        $discountData->setBaseAmount($discountQty * $effectiveBaseItemPrice);
        $discountData->setOriginalAmount($discountQty * $effectiveItemOriginalPrice);
        $discountData->setBaseOriginalAmount($discountQty * $effectiveBaseItemOriginalPrice);

        return $discountData;
    }
}
