<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Rule\Action\Discount;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;

class FreeGift extends AbstractDiscount
{
    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     * @return Data
     */
    public function calculate($rule, $item, $qty): Data
    {
        $discountData = $this->discountFactory->create();

        $giftSku = $rule->getData('gift_sku');
        if (!$giftSku || $item->getSku() !== $giftSku) {
            return $discountData;
        }

        $giftQty = (int)($rule->getData('gift_qty') ?: 1);
        $discountQty = min($qty, $giftQty);

        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);

        $discountData->setAmount($discountQty * $itemPrice);
        $discountData->setBaseAmount($discountQty * $baseItemPrice);
        $discountData->setOriginalAmount($discountQty * $itemOriginalPrice);
        $discountData->setBaseOriginalAmount($discountQty * $baseItemOriginalPrice);

        return $discountData;
    }

    /**
     * @param float $qty
     * @param Rule $rule
     * @return float
     */
    public function fixQuantity($qty, $rule): float
    {
        $giftQty = (int)($rule->getData('gift_qty') ?: 1);
        return min($qty, $giftQty);
    }
}
