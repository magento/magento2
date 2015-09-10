<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;

class Shipping extends CommonTaxCollector
{
    /**
     * Collect tax totals for shipping. The result can be used to calculate discount on shipping
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Address\Total $total
     * @return $this
     */
    public function collect(ShippingAssignmentInterface $shippingAssignment, Address\Total $total)
    {
        $storeId = $shippingAssignment->getShipping()->getAddress()->getQuote()->getStore()->getStoreId();
        $items = $shippingAssignment->getItems();
        if (!$items) {
            return $this;
        }

        //Add shipping
        $shippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, false);
        $baseShippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, true);
        if ($shippingDataObject == null || $baseShippingDataObject == null) {
            return $this;
        }

        $quoteDetails = $this->prepareQuoteDetails($shippingAssignment, [$shippingDataObject]);
        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $storeId);

        $baseQuoteDetails = $this->prepareQuoteDetails($shippingAssignment, [$baseShippingDataObject]);
        $baseTaxDetails = $this->taxCalculationService
            ->calculateTax($baseQuoteDetails, $storeId);

        $this->processShippingTaxInfo(
            $shippingAssignment,
            $total,
            $taxDetails->getItems()[self::ITEM_CODE_SHIPPING],
            $baseTaxDetails->getItems()[self::ITEM_CODE_SHIPPING]
        );

        return $this;
    }

    public function fetch(Address\Total $total)
    {
        return null;
    }
}
