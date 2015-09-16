<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model\Total\Quote;

use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Store\Model\Store;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

class WeeeTax extends Weee
{
    /**
     * Collect Weee taxes amount and prepare items prices for taxation and discount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface|\Magento\Quote\Model\Quote\Address $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        \Magento\Quote\Model\Quote\Address\Total\AbstractTotal::collect($quote, $shippingAssignment, $total);
        $this->_store = $quote->getStore();
        if (!$this->weeeData->isEnabled($this->_store)) {
            return $this;
        }

        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        //If Weee is not taxable, then the 'weee' collector has accumulated the non-taxable total values
        if (!$this->weeeData->isTaxable($this->_store)) {
            //Because Weee is not taxable:  Weee excluding tax == Weee including tax
            $weeeTotal = $total->getWeeeTotalExclTax();
            $weeeBaseTotal = $total->getWeeeBaseTotalExclTax();

            //Add to appropriate 'subtotal' or 'weee' accumulators
            $this->processTotalAmount($address, $total, $weeeTotal, $weeeBaseTotal, $weeeTotal, $weeeBaseTotal);
            return $this;
        }

        $weeeCodeToItemMap = $total->getWeeeCodeToItemMap();
        $extraTaxableDetails = $total->getExtraTaxableDetails();

        if (isset($extraTaxableDetails[self::ITEM_TYPE])) {
            foreach ($extraTaxableDetails[self::ITEM_TYPE] as $itemCode => $weeeAttributesTaxDetails) {
                $weeeCode = $weeeAttributesTaxDetails[0]['code'];
                $item = $weeeCodeToItemMap[$weeeCode];
                $this->weeeData->setApplied($item, []);

                $productTaxes = [];

                $totalValueInclTax = 0;
                $baseTotalValueInclTax = 0;
                $totalRowValueInclTax = 0;
                $baseTotalRowValueInclTax = 0;

                $totalValueExclTax = 0;
                $baseTotalValueExclTax = 0;
                $totalRowValueExclTax = 0;
                $baseTotalRowValueExclTax = 0;

                //Process each weee attribute of an item
                foreach ($weeeAttributesTaxDetails as $weeeTaxDetails) {
                    $weeeCode = $weeeTaxDetails[CommonTaxCollector::KEY_TAX_DETAILS_CODE];
                    $attributeCode = explode('-', $weeeCode)[1];

                    $valueExclTax = $weeeTaxDetails[CommonTaxCollector::KEY_TAX_DETAILS_PRICE_EXCL_TAX];
                    $baseValueExclTax = $weeeTaxDetails[CommonTaxCollector::KEY_TAX_DETAILS_BASE_PRICE_EXCL_TAX];
                    $valueInclTax = $weeeTaxDetails[CommonTaxCollector::KEY_TAX_DETAILS_PRICE_INCL_TAX];
                    $baseValueInclTax = $weeeTaxDetails[CommonTaxCollector::KEY_TAX_DETAILS_BASE_PRICE_INCL_TAX];

                    $rowValueExclTax = $weeeTaxDetails[CommonTaxCollector::KEY_TAX_DETAILS_ROW_TOTAL];
                    $baseRowValueExclTax = $weeeTaxDetails[CommonTaxCollector::KEY_TAX_DETAILS_BASE_ROW_TOTAL];
                    $rowValueInclTax = $weeeTaxDetails[CommonTaxCollector::KEY_TAX_DETAILS_ROW_TOTAL_INCL_TAX];
                    $baseRowValueInclTax = $weeeTaxDetails[CommonTaxCollector::KEY_TAX_DETAILS_BASE_ROW_TOTAL_INCL_TAX];

                    $totalValueInclTax += $valueInclTax;
                    $baseTotalValueInclTax += $baseValueInclTax;
                    $totalRowValueInclTax += $rowValueInclTax;
                    $baseTotalRowValueInclTax += $baseRowValueInclTax;

                    $totalValueExclTax += $valueExclTax;
                    $baseTotalValueExclTax += $baseValueExclTax;
                    $totalRowValueExclTax += $rowValueExclTax;
                    $baseTotalRowValueExclTax += $baseRowValueExclTax;

                    $productTaxes[] = [
                        'title' => $attributeCode, //TODO: fix this
                        'base_amount' => $baseValueExclTax,
                        'amount' => $valueExclTax,
                        'row_amount' => $rowValueExclTax,
                        'base_row_amount' => $baseRowValueExclTax,
                        'base_amount_incl_tax' => $baseValueInclTax,
                        'amount_incl_tax' => $valueInclTax,
                        'row_amount_incl_tax' => $rowValueInclTax,
                        'base_row_amount_incl_tax' => $baseRowValueInclTax,
                    ];
                }
                $item->setWeeeTaxAppliedAmount($totalValueExclTax)
                    ->setBaseWeeeTaxAppliedAmount($baseTotalValueExclTax)
                    ->setWeeeTaxAppliedRowAmount($totalRowValueExclTax)
                    ->setBaseWeeeTaxAppliedRowAmnt($baseTotalRowValueExclTax);

                $item->setWeeeTaxAppliedAmountInclTax($totalValueInclTax)
                    ->setBaseWeeeTaxAppliedAmountInclTax($baseTotalValueInclTax)
                    ->setWeeeTaxAppliedRowAmountInclTax($totalRowValueInclTax)
                    ->setBaseWeeeTaxAppliedRowAmntInclTax($baseTotalRowValueInclTax);

                $this->processTotalAmount(
                    $address,
                    $total,
                    $totalRowValueExclTax,
                    $baseTotalRowValueExclTax,
                    $totalRowValueInclTax,
                    $baseTotalRowValueInclTax
                );

                $total->setTotalAmount($this->getCode(), $this->weeeData->getTotalAmounts($items, $quote->getStore()));
                $this->weeeData->setApplied($item, array_merge($this->weeeData->getApplied($item), $productTaxes));
            }
        }
        return $this;
    }

    /**
     * Process row amount based on FPT total amount configuration setting
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param float $rowValueExclTax
     * @param float $baseRowValueExclTax
     * @param float $rowValueInclTax
     * @param float $baseRowValueInclTax
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function processTotalAmount(
        $address,
        $total,
        $rowValueExclTax,
        $baseRowValueExclTax,
        $rowValueInclTax,
        $baseRowValueInclTax
    ) {
        $totalCode = $this->weeeData->includeInSubtotal($this->_store) ? 'subtotal' : $this->getCode();
        $address->addTotalAmount($totalCode, $rowValueExclTax);
        $address->addBaseTotalAmount($totalCode, $baseRowValueExclTax);

        $address->setSubtotalInclTax($address->getSubtotalInclTax() + $rowValueInclTax);
        $address->setBaseSubtotalInclTax($address->getBaseSubtotalInclTax() + $baseRowValueInclTax);
        return $this;
    }

    /**
     * Fetch the Weee total amount for display in totals block when building the initial quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $weeeTotal = $total->getTotalAmount($this->getCode());
        if ($weeeTotal) {
            return [
                    'code' => $this->getCode(),
                    'title' => __('FPT'),
                    'value' => $weeeTotal,
                    'area' => null,
                ];
        }
        return null;
    }
}
