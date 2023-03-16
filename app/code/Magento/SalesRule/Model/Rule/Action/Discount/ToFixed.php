<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data as DiscountData;

class ToFixed extends AbstractDiscount
{
    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     * @return DiscountData
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var DiscountData $discountData */
        $discountData = $this->discountFactory->create();

        $store = $item->getQuote()->getStore();

        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);

        $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $store);

        $discountData->setAmount($qty * ($itemPrice - $quoteAmount));
        $discountData->setBaseAmount($qty * ($baseItemPrice - $rule->getDiscountAmount()));
        $discountData->setOriginalAmount($qty * ($itemOriginalPrice - $quoteAmount));
        $discountData->setBaseOriginalAmount($qty * ($baseItemOriginalPrice - $rule->getDiscountAmount()));

        return $discountData;
    }
}
