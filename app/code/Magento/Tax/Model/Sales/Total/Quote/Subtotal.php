<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Calculate items and address amounts including/excluding tax
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;

class Subtotal extends CommonTaxCollector
{
    /**
     * Calculate tax on product items. The result will be used to determine shipping
     * and discount later.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $items = $shippingAssignment->getItems();
        if (!$items) {
            return $this;
        }

        $store = $quote->getStore();
        $priceIncludesTax = $this->_config->priceIncludesTax($store);

        //Setup taxable items
        $itemDataObjects = $this->mapItems($shippingAssignment, $priceIncludesTax, false);
        $quoteDetails = $this->prepareQuoteDetails($shippingAssignment, $itemDataObjects);
        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $store->getStoreId());

        $itemDataObjects = $this->mapItems($shippingAssignment, $priceIncludesTax, true);
        $baseQuoteDetails = $this->prepareQuoteDetails($shippingAssignment, $itemDataObjects);
        $baseTaxDetails = $this->taxCalculationService
            ->calculateTax($baseQuoteDetails, $store->getStoreId());

        $itemsByType = $this->organizeItemTaxDetailsByType($taxDetails, $baseTaxDetails);

        if (isset($itemsByType[self::ITEM_TYPE_PRODUCT])) {
            $this->processProductItems($shippingAssignment, $itemsByType[self::ITEM_TYPE_PRODUCT], $total);
        }

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return null
     * @codeCoverageIgnore
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return null;
    }
}
