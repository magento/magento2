<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Calculate items and address amounts including/excluding tax
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Quote\Model\Quote\Address;

class Subtotal extends CommonTaxCollector
{
    /**
     * Calculate tax on product items. The result will be used to determine shipping
     * and discount later.
     *
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface|Address $shippingAssignment
     * @param Address\Total $total
     * @return $this
     */
    public function collect(\Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        parent::collect($shippingAssignment, $total);
        $items = $this->_getAddressItems($shippingAssignment);
        if (!$items) {
            return $this;
        }

        $priceIncludesTax = $this->_config->priceIncludesTax($shippingAssignment->getQuote()->getStore());

        //Setup taxable items
        $itemDataObjects = $this->mapItems($shippingAssignment, $priceIncludesTax, false);
        $quoteDetails = $this->prepareQuoteDetails($shippingAssignment, $itemDataObjects);
        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $shippingAssignment->getQuote()->getStore()->getStoreId());

        $itemDataObjects = $this->mapItems($shippingAssignment, $priceIncludesTax, true);
        $baseQuoteDetails = $this->prepareQuoteDetails($shippingAssignment, $itemDataObjects);
        $baseTaxDetails = $this->taxCalculationService
            ->calculateTax($baseQuoteDetails, $shippingAssignment->getQuote()->getStore()->getStoreId());

        $itemsByType = $this->organizeItemTaxDetailsByType($taxDetails, $baseTaxDetails);

        if (isset($itemsByType[self::ITEM_TYPE_PRODUCT])) {
            $this->processProductItems($shippingAssignment, $itemsByType[self::ITEM_TYPE_PRODUCT]);
        }

        return $this;
    }
}
