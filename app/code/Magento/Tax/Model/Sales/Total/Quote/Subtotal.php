<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Calculate items and address amounts including/excluding tax
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Sales\Model\Quote\Address;

class Subtotal extends CommonTaxCollector
{
    /**
     * Calculate tax on product items. The result will be used to determine shipping
     * and discount later.
     *
     * @param   Address $address
     * @return  $this
     */
    public function collect(Address $address)
    {
        parent::collect($address);
        $items = $this->_getAddressItems($address);
        if (!$items) {
            return $this;
        }

        $priceIncludesTax = $this->_config->priceIncludesTax($address->getQuote()->getStore());

        //Setup taxable items
        $itemDataObjects = $this->mapItems($address, $priceIncludesTax, false);
        $quoteDetails = $this->prepareQuoteDetails($address, $itemDataObjects);
        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $address->getQuote()->getStore()->getStoreId());

        $itemDataObjects = $this->mapItems($address, $priceIncludesTax, true);
        $baseQuoteDetails = $this->prepareQuoteDetails($address, $itemDataObjects);
        $baseTaxDetails = $this->taxCalculationService
            ->calculateTax($baseQuoteDetails, $address->getQuote()->getStore()->getStoreId());

        $itemsByType = $this->organizeItemTaxDetailsByType($taxDetails, $baseTaxDetails);

        if (isset($itemsByType[self::ITEM_TYPE_PRODUCT])) {
            $this->processProductItems($address, $itemsByType[self::ITEM_TYPE_PRODUCT]);
        }

        return $this;
    }
}
