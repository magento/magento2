<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

/**
 * Checks that prices including tax in order are correct on backend.
 */
class AssertOrderTaxOnBackendIncludingTax extends AbstractAssertOrderTaxOnBackend
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
     * @param $actualPrices
     * @return array
     */
    public function getOrderTotals($actualPrices)
    {
        $viewBlock = $this->salesOrderView->getOrderTotalsBlock();
        $actualPrices['subtotal_excl_tax'] = null;
        $actualPrices['subtotal_incl_tax'] = $viewBlock->getSubtotal();

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
     * @param $actualPrices
     * @return array
     */
    public function getInvoiceNewTotals($actualPrices)
    {
        $totalsBlock = $this->orderInvoiceNew->getTotalsBlock();
        $actualPrices['subtotal_excl_tax'] = null;
        $actualPrices['subtotal_incl_tax'] = $totalsBlock->getSubtotal();

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
     * @param $actualPrices
     * @return array
     */
    public function getCreditMemoNewTotals($actualPrices)
    {
        $totalsBlock = $this->orderCreditMemoNew->getTotalsBlock();
        $actualPrices['subtotal_excl_tax'] = null;
        $actualPrices['subtotal_incl_tax'] = $totalsBlock->getSubtotal();

        $actualPrices['discount'] = $totalsBlock->getDiscount();

        $actualPrices['shipping_excl_tax'] = $totalsBlock->getShippingExclTax();
        $actualPrices['shipping_incl_tax'] = $totalsBlock->getShippingInclTax();
        $actualPrices['tax'] = $totalsBlock->getTax();
        $actualPrices['grand_total_excl_tax'] = $totalsBlock->getGrandTotalExclTax();
        $actualPrices['grand_total_incl_tax'] = $totalsBlock->getGrandTotalInclTax();

        return $actualPrices;
    }
}
