<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Quote\Item\Plugin;

use Magento\Quote\Model\Quote\Item\CartItemPersister;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Plugin for persisting discounts on Cart Item
 */
class Discount
{

    private $json;

    /**
     * @param Json $json
     */
    public function __construct(Json $json)
    {
        $this->json = $json;
    }

    /**
     * Plugin method for persisting data from extension attributes
     *
     * @param CartItemPersister $subject
     * @param CartInterface $quote
     * @param CartItemInterface $cartItem
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(CartItemPersister $subject, CartInterface $quote, CartItemInterface $cartItem)
    {
        $cartExtension = $cartItem->getExtensionAttributes();
        $discounts = $cartExtension->getDiscounts();
        $serializedDiscount = [];
        if ($discounts) {
            foreach ($discounts as $value) {
                $discount = $value->getDiscountData();
                $discountData = [
                    "amount" => $discount->getAmount(),
                    "baseAmount" => $discount->getBaseAmount(),
                    "originalAmount" => $discount->getOriginalAmount(),
                    "baseOriginalAmount" => $discount->getBaseOriginalAmount(),
                ];
                $serializedDiscount[] = [
                    'discount' => $this->json->serialize($discountData),
                    'rule' => $value->getRuleLabel(),
                    'ruleID' => $value->getRuleID(),
                ];
            }
            $cartItem->setDiscounts($this->json->serialize($serializedDiscount));
        }
        return [$quote, $cartItem];
    }
}
