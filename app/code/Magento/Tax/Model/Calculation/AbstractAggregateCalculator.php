<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Model\Calculation;

use Magento\Tax\Model\Calculation;
use Magento\Customer\Service\V1\Data\Address;
use Magento\Tax\Service\V1\Data\QuoteDetails\Item as QuoteDetailsItem;

abstract class AbstractAggregateCalculator extends AbstractCalculator
{
    /**
     * {@inheritdoc}
     */
    protected function calculateWithTaxInPrice(QuoteDetailsItem $item, $quantity)
    {
        $taxRateRequest = $this->getAddressRateRequest()->setProductClassId(
            $this->taxClassService->getTaxClassId($item->getTaxClassKey())
        );
        $rate = $this->calculationTool->getRate($taxRateRequest);
        $storeRate = $storeRate = $this->calculationTool->getStoreRate($taxRateRequest, $this->storeId);

        // Calculate $rowTotalInclTax
        $priceInclTax = $this->calculationTool->round($item->getUnitPrice());
        $rowTotalInclTax = $priceInclTax * $quantity;
        if (!$this->isSameRateAsStore($rate, $storeRate)) {
            $priceInclTax = $this->calculatePriceInclTax($priceInclTax, $storeRate, $rate);
            $rowTotalInclTax = $priceInclTax * $quantity;
        }
        $rowTaxExact = $this->calculationTool->calcTaxAmount($rowTotalInclTax, $rate, true, false);
        $rowTax = $this->roundAmount($rowTaxExact, $rate, true);
        $rowTotal = $rowTotalInclTax - $rowTax;
        $price = $this->calculationTool->round($rowTotal / $quantity);

        //Handle discount
        $discountTaxCompensationAmount = 0;
        $applyTaxAfterDiscount = $this->config->applyTaxAfterDiscount($this->storeId);
        $discountAmount = $item->getDiscountAmount();
        if ($discountAmount && $applyTaxAfterDiscount) {
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
                self::KEY_TAX_AFTER_DISCOUNT_DELTA_ROUNDING
            );
            // Set discount tax compensation
            $discountTaxCompensationAmount = $rowTax - $rowTaxAfterDiscount;
            $rowTax = $rowTaxAfterDiscount;
        }

        // Calculate applied taxes
        /** @var  \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTax[] $appliedTaxes */
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
    protected function calculateWithTaxNotInPrice(QuoteDetailsItem $item, $quantity)
    {
        $taxRateRequest = $this->getAddressRateRequest()->setProductClassId(
            $this->taxClassService->getTaxClassId($item->getTaxClassKey())
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
            $rowTaxPerRate = $this->roundAmount($rowTaxPerRate, $taxRate, false);
            $rowTaxAfterDiscount = $rowTaxPerRate;

            //Handle discount
            if ($discountAmount && $applyTaxAfterDiscount) {
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
                    $taxRate,
                    false,
                    self::KEY_TAX_AFTER_DISCOUNT_DELTA_ROUNDING
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
        $priceInclTax = $this->calculationTool->round($rowTotalInclTax / $quantity);

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
     * @return float
     */
    abstract protected function roundAmount(
        $amount,
        $rate = null,
        $direction = null,
        $type = self::KEY_REGULAR_DELTA_ROUNDING
    );
}
