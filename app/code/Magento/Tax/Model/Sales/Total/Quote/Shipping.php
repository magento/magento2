<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Model\Sales\Total\Quote;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;

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

        $shippingAddress = $shippingAssignment->getShipping()->getAddress();
        $quoteDetails = $this->prepareQuoteDetails($shippingAssignment, [$shippingDataObject]);
        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $storeId);
        $taxDetailsItems = $taxDetails->getItems()[self::ITEM_CODE_SHIPPING];

        $baseQuoteDetails = $this->prepareQuoteDetails($shippingAssignment, [$baseShippingDataObject]);
        $baseTaxDetails = $this->taxCalculationService
            ->calculateTax($baseQuoteDetails, $storeId);
        $baseTaxDetailsItems = $baseTaxDetails->getItems()[self::ITEM_CODE_SHIPPING];

        $shippingAddress->setShippingAmount($taxDetailsItems->getRowTotal());
        $shippingAddress->setBaseShippingAmount($baseTaxDetailsItems->getRowTotal());

        $this->processShippingTaxInfo(
            $shippingAssignment,
            $total,
            $taxDetailsItems,
            $baseTaxDetailsItems
        );

        return $this;
    }

    /**
     * Fetch shipping including tax
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
