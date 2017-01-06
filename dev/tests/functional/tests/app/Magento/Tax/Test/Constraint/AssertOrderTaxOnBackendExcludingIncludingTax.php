<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

/**
 * Checks that prices displayed excluding and including tax in order are correct on backend.
 */
class AssertOrderTaxOnBackendExcludingIncludingTax extends AbstractAssertOrderTaxOnBackend
{
    /**
     * Constraint severeness.
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Get order totals.
     *
     * @param array $actualPrices
     * @return array
     */
    public function getOrderTotals($actualPrices)
    {
        $viewBlock = $this->salesOrderView->getOrderTotalsBlock();
        $actualPrices['subtotal_excl_tax'] = $viewBlock->getSubtotalExclTax();
        $actualPrices['subtotal_incl_tax'] = $viewBlock->getSubtotalInclTax();

        $actualPrices['discount'] = $viewBlock->getDiscount();

        $actualPrices['shipping_excl_tax'] = $viewBlock->getShippingExclTax();
        $actualPrices['shipping_incl_tax'] = $viewBlock->getShippingInclTax();
        $actualPrices['tax'] = $viewBlock->getTax();
        $actualPrices['grand_total_excl_tax'] = $viewBlock->getGrandTotalExclTax();
        $actualPrices['grand_total_incl_tax'] = $viewBlock->getGrandTotalInclTax();

        return $actualPrices;
    }

    /**
     * Get invoice new totals.
     *
     * @param array $actualPrices
     * @return array
     */
    public function getInvoiceNewTotals($actualPrices)
    {
        $totalsBlock = $this->orderInvoiceNew->getTotalsBlock();
        $actualPrices['subtotal_excl_tax'] = $totalsBlock->getSubtotalExclTax();
        $actualPrices['subtotal_incl_tax'] = $totalsBlock->getSubtotalInclTax();

        $actualPrices['discount'] = $totalsBlock->getDiscount();

        $actualPrices['shipping_excl_tax'] = $totalsBlock->getShippingExclTax();
        $actualPrices['shipping_incl_tax'] = $totalsBlock->getShippingInclTax();
        $actualPrices['tax'] = $totalsBlock->getTax();

        $actualPrices['grand_total_excl_tax'] = $totalsBlock->getGrandTotalExclTax();
        $actualPrices['grand_total_incl_tax'] = $totalsBlock->getGrandTotalInclTax();

        return $actualPrices;
    }

    /**
     * Get Credit Memo new totals.
     *
     * @param array $actualPrices
     * @return array
     */
    public function getCreditMemoNewTotals($actualPrices)
    {
        $totalsBlock = $this->orderCreditMemoNew->getTotalsBlock();
        $actualPrices['subtotal_excl_tax'] = $totalsBlock->getSubtotalExclTax();
        $actualPrices['subtotal_incl_tax'] = $totalsBlock->getSubtotalInclTax();

        $actualPrices['discount'] = $totalsBlock->getDiscount();

        $actualPrices['tax'] = $totalsBlock->getTax();
        $actualPrices['grand_total_excl_tax'] = $totalsBlock->getGrandTotalExclTax();
        $actualPrices['grand_total_incl_tax'] = $totalsBlock->getGrandTotalInclTax();

        return $actualPrices;
    }
}
