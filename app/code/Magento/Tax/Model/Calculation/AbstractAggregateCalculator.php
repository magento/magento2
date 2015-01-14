<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Calculation;

use Magento\Tax\Api\Data\QuoteDetailsItemInterface;

abstract class AbstractAggregateCalculator extends AbstractCalculator
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

        $discountTaxCompensationAmount = 0;
        $applyTaxAfterDiscount = $this->config->applyTaxAfterDiscount($this->storeId);
        $discountAmount = $item->getDiscountAmount();

        // Calculate $rowTotalInclTax
        $priceInclTax = $this->calculationTool->round($item->getUnitPrice());
        $rowTotalInclTax = $priceInclTax * $quantity;
        if (!$this->isSameRateAsStore($rate, $storeRate)) {
            $priceInclTax = $this->calculatePriceInclTax($priceInclTax, $storeRate, $rate);
            $rowTotalInclTax = $priceInclTax * $quantity;
        }
        $rowTaxExact = $this->calculationTool->calcTaxAmount($rowTotalInclTax, $rate, true, false);
        $deltaRoundingType = self::KEY_REGULAR_DELTA_ROUNDING;
        if ($applyTaxAfterDiscount) {
            $deltaRoundingType = self::KEY_TAX_BEFORE_DISCOUNT_DELTA_ROUNDING;
        }
        $rowTax = $this->roundAmount($rowTaxExact, $rate, true, $deltaRoundingType, $round);
        $rowTotal = $rowTotalInclTax - $rowTax;
        $price = $rowTotal / $quantity;
        if ($round) {
            $price = $this->calculationTool->round($price);
        }

        //Handle discount
        if ($applyTaxAfterDiscount) {
            //TODO: handle originalDiscountAmount
            $taxableAmount = max($rowTotalInclTax - $discountAmount, 0);
            $rowTaxAfterDiscount = $this->calculationTool->calcTaxAmount(
                $taxableAmount,
                $rate,
                true,
                false
            );
            $rowTaxAfterDiscount = $this->roundAmount(
                $rowTaxAfterDiscount,
                $rate,
                true,
                self::KEY_REGULAR_DELTA_ROUNDING,
                $round
            );
            // Set discount tax compensation
            $discountTaxCompensationAmount = $rowTax - $rowTaxAfterDiscount;
            $rowTax = $rowTaxAfterDiscount;
        }

        // Calculate applied taxes
        /** @var  \Magento\Tax\Api\Data\AppliedTaxInterface[] $appliedTaxes */
        $appliedTaxes = [];
        $appliedRates = $this->calculationTool->getAppliedRates($taxRateRequest);
        $appliedTaxes = $this->getAppliedTaxes($rowTax, $rate, $appliedRates);

        $this->taxDetailsItemBuilder->setCode($item->getCode());
        $this->taxDetailsItemBuilder->setType($item->getType());
        $this->taxDetailsItemBuilder->setRowTax($rowTax);
        $this->taxDetailsItemBuilder->setPrice($price);
        $this->taxDetailsItemBuilder->setPriceInclTax($priceInclTax);
        $this->taxDetailsItemBuilder->setRowTotal($rowTotal);
        $this->taxDetailsItemBuilder->setRowTotalInclTax($rowTotalInclTax);
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

        // Calculate $rowTotal
        $price = $this->calculationTool->round($item->getUnitPrice());
        $rowTotal = $price * $quantity;
        $rowTaxes = [];
        $rowTaxesBeforeDiscount = [];
        $appliedTaxes = [];
        //Apply each tax rate separately
        foreach ($appliedRates as $appliedRate) {
            $taxId = $appliedRate['id'];
            $taxRate = $appliedRate['percent'];
            $rowTaxPerRate = $this->calculationTool->calcTaxAmount($rowTotal, $taxRate, false, false);
            $deltaRoundingType = self::KEY_REGULAR_DELTA_ROUNDING;
            if ($applyTaxAfterDiscount) {
                $deltaRoundingType = self::KEY_TAX_BEFORE_DISCOUNT_DELTA_ROUNDING;
            }
            $rowTaxPerRate = $this->roundAmount($rowTaxPerRate, $taxId, false, $deltaRoundingType, $round);
            $rowTaxAfterDiscount = $rowTaxPerRate;

            //Handle discount
            if ($applyTaxAfterDiscount) {
                //TODO: handle originalDiscountAmount
                $taxableAmount = max($rowTotal - $discountAmount, 0);
                $rowTaxAfterDiscount = $this->calculationTool->calcTaxAmount(
                    $taxableAmount,
                    $taxRate,
                    false,
                    false
                );
                $rowTaxAfterDiscount = $this->roundAmount(
                    $rowTaxAfterDiscount,
                    $taxId,
                    false,
                    self::KEY_REGULAR_DELTA_ROUNDING,
                    $round
                );
            }
            $appliedTaxes[$taxId] = $this->getAppliedTax(
                $rowTaxAfterDiscount,
                $appliedRate
            );

            $rowTaxes[] = $rowTaxAfterDiscount;
            $rowTaxesBeforeDiscount[] = $rowTaxPerRate;
        }
        $rowTax = array_sum($rowTaxes);
        $rowTaxBeforeDiscount = array_sum($rowTaxesBeforeDiscount);
        $rowTotalInclTax = $rowTotal + $rowTaxBeforeDiscount;
        $priceInclTax = $rowTotalInclTax / $quantity;
        if ($round) {
            $priceInclTax = $this->calculationTool->round($priceInclTax);
        }

        $this->taxDetailsItemBuilder->setCode($item->getCode());
        $this->taxDetailsItemBuilder->setType($item->getType());
        $this->taxDetailsItemBuilder->setRowTax($rowTax);
        $this->taxDetailsItemBuilder->setPrice($price);
        $this->taxDetailsItemBuilder->setPriceInclTax($priceInclTax);
        $this->taxDetailsItemBuilder->setRowTotal($rowTotal);
        $this->taxDetailsItemBuilder->setRowTotalInclTax($rowTotalInclTax);
        $this->taxDetailsItemBuilder->setDiscountTaxCompensationAmount($discountTaxCompensationAmount);
        $this->taxDetailsItemBuilder->setAssociatedItemCode($item->getAssociatedItemCode());
        $this->taxDetailsItemBuilder->setTaxPercent($rate);
        $this->taxDetailsItemBuilder->setAppliedTaxes($appliedTaxes);
        return $this->taxDetailsItemBuilder->create();
    }

    /**
     * Round amount
     *
     * @param float $amount
     * @param null|float $rate
     * @param null|bool $direction
     * @param string $type
     * @param bool $round
     * @return float
     */
    abstract protected function roundAmount(
        $amount,
        $rate = null,
        $direction = null,
        $type = self::KEY_REGULAR_DELTA_ROUNDING,
        $round = true
    );
}
