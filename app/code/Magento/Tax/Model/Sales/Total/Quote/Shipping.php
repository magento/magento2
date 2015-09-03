<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Quote\Model\Quote\Address;

class Shipping extends CommonTaxCollector
{
    /**
     * Collect tax totals for shipping. The result can be used to calculate discount on shipping
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

        //Add shipping
        $shippingDataObject = $this->getShippingDataObject($shippingAssignment, false);
        $baseShippingDataObject = $this->getShippingDataObject($shippingAssignment, true);
        if ($shippingDataObject == null || $baseShippingDataObject == null) {
            return $this;
        }

        $quoteDetails = $this->prepareQuoteDetails($shippingAssignment, [$shippingDataObject]);
        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $shippingAssignment->getQuote()->getStore()->getStoreId());

        $baseQuoteDetails = $this->prepareQuoteDetails($shippingAssignment, [$baseShippingDataObject]);
        $baseTaxDetails = $this->taxCalculationService
            ->calculateTax($baseQuoteDetails, $shippingAssignment->getQuote()->getStore()->getStoreId());

        $this->processShippingTaxInfo(
            $shippingAssignment,
            $taxDetails->getItems()[self::ITEM_CODE_SHIPPING],
            $baseTaxDetails->getItems()[self::ITEM_CODE_SHIPPING]
        );

        return $this;
    }
}
