<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Bundle\Plugin\Quote;

use Magento\Bundle\Model\Product\OriginalPrice;
use Magento\Bundle\Model\Product\Type;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total\Subtotal;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;

/**
 * Update bundle base original price
 */
class UpdateBundleQuoteItemBaseOriginalPrice
{
    /**
     * @param OriginalPrice $price
     */
    public function __construct(
        private readonly OriginalPrice $price
    ) {
    }

    /**
     * Update bundle base original price
     *
     * @param Subtotal $subject
     * @param Subtotal $result
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     *
     * @return Subtotal
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCollect(
        Subtotal $subject,
        Subtotal $result,
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): Subtotal {
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            if ($quoteItem->getProductType() === Type::TYPE_CODE) {
                $price = $quoteItem->getProduct()->getPrice();
                $price += $this->price->getTotalBundleItemsOriginalPrice($quoteItem->getProduct());
                $quoteItem->setBaseOriginalPrice($price);
            }
        }
        return $result;
    }
}
