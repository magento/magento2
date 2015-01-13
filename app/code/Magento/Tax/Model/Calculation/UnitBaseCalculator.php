<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Calculation;

use Magento\Tax\Api\Data\QuoteDetailsItemInterface;

class UnitBaseCalculator extends AbstractCalculator
{
    /**
     * {@inheritdoc}
     */
    protected function calculateWithTaxInPrice(QuoteDetailsItemInterface $item, $quantity, $round = true)
    {
        $taxRateRequest = $this->getAddressRateRequest()->setProductClassId(
            $this->taxClassManagement->getTaxClassId($item->getTaxClassKey())
        );
        $rate = $this->calculationTool->getRate($taxRateRequest);
        $storeRate = $storeRate = $this->calculationTool->getStoreRate($taxRateRequest, $this->storeId);

        // Calculate $priceInclTax
        $priceInclTax = $this->calculationTool->round($item->getUnitPrice());
        if (!$this->isSameRateAsStore($rate, $storeRate)) {
            $priceInclTax = $this->calculatePriceInclTax($priceInclTax, $storeRate, $rate);
        }
        $uniTax = $this->calculationTool->calcTaxAmount($priceInclTax, $rate, true, true);
        $price = $priceInclTax - $uniTax;

        //Handle discount
        $discountTaxCompensationAmount = 0;
        $applyTaxAfterDiscount = $this->config->applyTaxAfterDiscount($this->storeId);
        $discountAmount = $item->getDiscountAmount();
        if ($discountAmount && $applyTaxAfterDiscount) {
            //TODO: handle originalDiscountAmount
            $unitDiscountAmount = $discountAmount / $quantity;
            $taxableAmount = max($priceInclTax - $unitDiscountAmount, 0);
            $unitTaxAfterDiscount = $this->calculationTool->calcTaxAmount(
                $taxableAmount,
                $rate,
                true,
                true
            );

            // Set discount tax compensation
            $unitDiscountTaxCompensationAmount = $uniTax - $unitTaxAfterDiscount;
            $discountTaxCompensationAmount = $unitDiscountTaxCompensationAmount * $quantity;
            $uniTax = $unitTaxAfterDiscount;
        }
        $rowTax = $uniTax * $quantity;

        // Calculate applied taxes
        /** @var  \Magento\Tax\Api\Data\AppliedTaxInterface[] $appliedTaxes */
        $appliedRates = $this->calculationTool->getAppliedRates($taxRateRequest);
        $appliedTaxes = $this->getAppliedTaxes($rowTax, $rate, $appliedRates);

        $this->taxDetailsItemBuilder->setCode($item->getCode());
        $this->taxDetailsItemBuilder->setType($item->getType());
        $this->taxDetailsItemBuilder->setRowTax($rowTax);
        $this->taxDetailsItemBuilder->setPrice($price);
        $this->taxDetailsItemBuilder->setPriceInclTax($priceInclTax);
        $this->taxDetailsItemBuilder->setRowTotal($price * $quantity);
        $this->taxDetailsItemBuilder->setRowTotalInclTax($priceInclTax * $quantity);
        $this->taxDetailsItemBuilder->setDiscountTaxCompensationAmount($discountTaxCompensationAmount);
        $this->taxDetailsItemBuilder->setAssociatedItemCode($item->getAssociatedItemCode());
        $this->taxDetailsItemBuilder->setTaxPercent($rate);
        $this->taxDetailsItemBuilder->setAppliedTaxes($appliedTaxes);
        return $this->taxDetailsItemBuilder->create();
    }

    /**
     * {@inheritdoc}
     */
    protected function calculateWithTaxNotInPrice(QuoteDetailsItemInterface $item, $quantity, $round = true)
    {
        $taxRateRequest = $this->getAddressRateRequest()->setProductClassId(
            $this->taxClassManagement->getTaxClassId($item->getTaxClassKey())
        );
        $rate = $this->calculationTool->getRate($taxRateRequest);
        $appliedRates = $this->calculationTool->getAppliedRates($taxRateRequest);

        $applyTaxAfterDiscount = $this->config->applyTaxAfterDiscount($this->storeId);
        $discountAmount = $item->getDiscountAmount();
        $discountTaxCompensationAmount = 0;

        // Calculate $price
        $price = $this->calculationTool->round($item->getUnitPrice());
        $unitTaxes = [];
        $unitTaxesBeforeDiscount = [];
        $appliedTaxes = [];
        //Apply each tax rate separately
        foreach ($appliedRates as $appliedRate) {
            $taxId = $appliedRate['id'];
            $taxRate = $appliedRate['percent'];
            $unitTaxPerRate = $this->calculationTool->calcTaxAmount($price, $taxRate, false);
            $unitTaxAfterDiscount = $unitTaxPerRate;

            //Handle discount
            if ($discountAmount && $applyTaxAfterDiscount) {
                //TODO: handle originalDiscountAmount
                $unitDiscountAmount = $discountAmount / $quantity;
                $taxableAmount = max($price - $unitDiscountAmount, 0);
                $unitTaxAfterDiscount = $this->calculationTool->calcTaxAmount(
                    $taxableAmount,
                    $taxRate,
                    false,
                    true
                );
            }
            $appliedTaxes[$taxId] = $this->getAppliedTax(
                $unitTaxAfterDiscount * $quantity,
                $appliedRate
            );

            $unitTaxes[] = $unitTaxAfterDiscount;
            $unitTaxesBeforeDiscount[] = $unitTaxPerRate;
        }
        $unitTax = array_sum($unitTaxes);
        $unitTaxBeforeDiscount = array_sum($unitTaxesBeforeDiscount);

        $rowTax = $unitTax * $quantity;
        $priceInclTax = $price + $unitTaxBeforeDiscount;

        $this->taxDetailsItemBuilder->setCode($item->getCode());
        $this->taxDetailsItemBuilder->setType($item->getType());
        $this->taxDetailsItemBuilder->setRowTax($rowTax);
        $this->taxDetailsItemBuilder->setPrice($price);
        $this->taxDetailsItemBuilder->setPriceInclTax($priceInclTax);
        $this->taxDetailsItemBuilder->setRowTotal($price * $quantity);
        $this->taxDetailsItemBuilder->setRowTotalInclTax($priceInclTax * $quantity);
        $this->taxDetailsItemBuilder->setDiscountTaxCompensationAmount($discountTaxCompensationAmount);
        $this->taxDetailsItemBuilder->setAssociatedItemCode($item->getAssociatedItemCode());
        $this->taxDetailsItemBuilder->setTaxPercent($rate);
        $this->taxDetailsItemBuilder->setAppliedTaxes($appliedTaxes);
        return $this->taxDetailsItemBuilder->create();
    }
}
