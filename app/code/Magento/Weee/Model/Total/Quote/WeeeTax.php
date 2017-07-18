<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
            $this->processTotalAmount($total, $weeeTotal, $weeeBaseTotal, $weeeTotal, $weeeBaseTotal);
            return $this;
        }
        
        $extraTaxableDetails = $total->getExtraTaxableDetails();

        if (isset($extraTaxableDetails[self::ITEM_TYPE])) {
            //Get mapping from weeeCode to item
            $weeeCodeToItemMap = $total->getWeeeCodeToItemMap();

            //Create mapping from item to weeeCode
            $itemToWeeeCodeMap = $this->createItemToWeeeCodeMapping($weeeCodeToItemMap);

            //Create mapping from weeeCode to weeeTaxDetails
            $weeeCodeToWeeeTaxDetailsMap = [];
            foreach ($extraTaxableDetails[self::ITEM_TYPE] as $weeeAttributesTaxDetails) {
                foreach ($weeeAttributesTaxDetails as $weeeTaxDetails) {
                    $weeeCode = $weeeTaxDetails['code'];
                    $weeeCodeToWeeeTaxDetailsMap[$weeeCode] = $weeeTaxDetails;
                }
            }

            //Process each item that has taxable weee
            foreach ($itemToWeeeCodeMap as $mapping) {
                $item = $mapping['item'];

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

                //Process each taxed weee attribute of an item
                foreach ($mapping['weeeCodes'] as $weeeCode) {
                    if (!array_key_exists($weeeCode, $weeeCodeToWeeeTaxDetailsMap)) {
                        //Need to ensure that everyone is in sync for which weee code to process
                        continue;
                    }
                    $weeeTaxDetails = $weeeCodeToWeeeTaxDetailsMap[$weeeCode];
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
                    $total,
                    $totalRowValueExclTax,
                    $baseTotalRowValueExclTax,
                    $totalRowValueInclTax,
                    $baseTotalRowValueInclTax
                );

                $this->weeeData->setApplied($item, array_merge($this->weeeData->getApplied($item), $productTaxes));
            }
        }
        return $this;
    }

    /**
     * Given a mapping from a weeeCode to an item, create a mapping from the item to the list of weeeCodes.
     *
     * Example of input:
     *  [
     *      "weeeCode1" -> item1,
     *      "weeeCode2" -> item1,
     *      ...
     *      "weeeCodeX" -> item22,
     *      "weeeCodeY" -> item22,
     *      ...
     *   ]
     *
     * Example of output:
     *  [
     *    item1Id  -> [ "item"  -> item1,
     *                  "weeeCodes" -> [weeeCode1, weeeCode2, ...]
     *                ],
     *    ...
     *    item22Id -> [ "item"  -> item22,
     *                  "weeeCodes" -> [weeeCodeX, weeeCodeY, ...]
     *                ],
     *    ...
     *  ]
     *
     * @param array $weeeCodeToItemMap
     * @return array
     */
    protected function createItemToWeeeCodeMapping($weeeCodeToItemMap)
    {
        $itemToCodeMap = [];
        foreach ($weeeCodeToItemMap as $weeeCode => $item) {
            $key = spl_object_hash($item);  // note: $item->getItemId() can be null
            if (!array_key_exists($key, $itemToCodeMap)) {
                //Create the initial structure for this item
                $itemToCodeMap[$key] = ['item' => $item, 'weeeCodes' => [$weeeCode]];
            } else {
                //Append the weeeCode to the existing structure
                $itemToCodeMap[$key]['weeeCodes'][] = $weeeCode;
            }
        }
        return $itemToCodeMap;
    }

    /**
     * Process row amount based on FPT total amount configuration setting
     *
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @param float $rowValueExclTax
     * @param float $baseRowValueExclTax
     * @param float $rowValueInclTax
     * @param float $baseRowValueInclTax
     * @return $this
     */
    protected function processTotalAmount(
        $total,
        $rowValueExclTax,
        $baseRowValueExclTax,
        $rowValueInclTax,
        $baseRowValueInclTax
    ) {
        if ($this->weeeData->includeInSubtotal($this->_store)) {
            $total->addTotalAmount('subtotal', $rowValueExclTax);
            $total->addBaseTotalAmount('subtotal', $baseRowValueExclTax);
        } else {
            $total->addTotalAmount('weee', $rowValueExclTax);
            $total->addBaseTotalAmount('weee', $baseRowValueExclTax);
        }

        $total->setSubtotalInclTax($total->getSubtotalInclTax() + $rowValueInclTax);
        $total->setBaseSubtotalInclTax($total->getBaseSubtotalInclTax() + $baseRowValueInclTax);
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
        /** @var $items \Magento\Sales\Model\Order\Item[] */
        $items = isset($total['address_quote_items']) ? $total['address_quote_items'] : [];

        $weeeTotal = $this->weeeData->getTotalAmounts($items, $quote->getStore());
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
