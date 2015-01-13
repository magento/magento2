<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model\Total\Quote;

use Magento\Sales\Model\Quote\Address\Total\AbstractTotal;
use Magento\Store\Model\Store;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

class WeeeTax extends Weee
{
    /**
     * Collect Weee taxes amount and prepare items prices for taxation and discount
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  $this
     */
    public function collect(\Magento\Sales\Model\Quote\Address $address)
    {
        \Magento\Sales\Model\Quote\Address\Total\AbstractTotal::collect($address);
        $this->_store = $address->getQuote()->getStore();
        if (!$this->weeeData->isEnabled($this->_store)) {
            return $this;
        }

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        //If Weee is not taxable, then the 'weee' collector has accumulated the non-taxable total values
        if (!$this->weeeData->isTaxable($this->_store)) {
            //Because Weee is not taxable:  Weee excluding tax == Weee including tax
            $weeeTotal = $address->getWeeeTotalExclTax();
            $weeeBaseTotal = $address->getWeeeBaseTotalExclTax();

            //Add to appropriate 'subtotal' or 'weee' accumulators
            $this->processTotalAmount($address, $weeeTotal, $weeeBaseTotal, $weeeTotal, $weeeBaseTotal);
            return $this;
        }

        $weeeCodeToItemMap = $address->getWeeeCodeToItemMap();
        $extraTaxableDetails = $address->getExtraTaxableDetails();

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
     * Process row amount based on FPT total amount configuration setting
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @param   float $rowValueExclTax
     * @param   float $baseRowValueExclTax
     * @param   float $rowValueInclTax
     * @param   float $baseRowValueInclTax
     * @return  $this
     */
    protected function processTotalAmount(
        $address,
        $rowValueExclTax,
        $baseRowValueExclTax,
        $rowValueInclTax,
        $baseRowValueInclTax
    ) {
        if ($this->weeeData->includeInSubtotal($this->_store)) {
            $address->addTotalAmount('subtotal', $rowValueExclTax);
            $address->addBaseTotalAmount('subtotal', $baseRowValueExclTax);
        } else {
            $address->addTotalAmount('weee', $rowValueExclTax);
            $address->addBaseTotalAmount('weee', $baseRowValueExclTax);
        }

        $address->setSubtotalInclTax($address->getSubtotalInclTax() + $rowValueInclTax);
        $address->setBaseSubtotalInclTax($address->getBaseSubtotalInclTax() + $baseRowValueInclTax);
        return $this;
    }

    /**
     * Fetch the Weee total amount for display in totals block when building the initial quote
     *
     * @param   \Magento\Sales\Model\Quote\Address $address
     * @return  $this
     */
    public function fetch(\Magento\Sales\Model\Quote\Address $address)
    {
        /** @var $items \Magento\Sales\Model\Order\Item[] */
        $items = $this->_getAddressItems($address);
        $store = $address->getQuote()->getStore();

        $weeeTotal = $this->weeeData->getTotalAmounts($items, $store);
        if ($weeeTotal) {
            $address->addTotal(
                [
                    'code' => $this->getCode(),
                    'title' => __('FPT'),
                    'value' => $weeeTotal,
                    'area' => null,
                ]
            );
        }
        return $this;
    }
}
