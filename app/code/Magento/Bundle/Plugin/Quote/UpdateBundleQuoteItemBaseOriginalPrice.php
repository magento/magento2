<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
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
