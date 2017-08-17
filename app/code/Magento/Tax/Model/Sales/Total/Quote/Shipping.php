<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;

/**
 * Class \Magento\Tax\Model\Sales\Total\Quote\Shipping
 *
 */
class Shipping extends CommonTaxCollector
{
    /**
     * Collect tax totals for shipping. The result can be used to calculate discount on shipping
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
        $storeId = $quote->getStoreId();
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

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array|null
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if ($total->getShippingInclTax()) {
            return [
                'code' => 'shipping',
                'shipping_incl_tax' => $total->getShippingInclTax()
            ];
        }
        return null;
    }
}
