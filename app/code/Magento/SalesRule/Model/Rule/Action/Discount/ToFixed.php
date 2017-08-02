<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Action\Discount;

/**
 * Class \Magento\SalesRule\Model\Rule\Action\Discount\ToFixed
 *
 * @since 2.0.0
 */
class ToFixed extends AbstractDiscount
{
    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     * @since 2.0.0
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
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
