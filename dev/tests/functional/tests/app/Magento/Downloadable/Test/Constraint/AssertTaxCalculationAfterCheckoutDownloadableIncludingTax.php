<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Constraint;

/**
 * Checks that prices incl tax on order review and customer order pages are equal to specified in dataset.
 */
class AssertTaxCalculationAfterCheckoutDownloadableIncludingTax extends
 AbstractAssertTaxCalculationAfterCheckoutDownloadable
{
    /**
     * Constraint severeness.
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Get review totals.
     *
     * @param $actualPrices
     * @return array
     */
    public function getReviewTotals($actualPrices)
    {
        $reviewBlock = $this->checkoutOnepage->getReviewBlock();
        $actualPrices['subtotal_excl_tax'] = null;
        $actualPrices['subtotal_incl_tax'] = $reviewBlock->getSubtotal();
        $actualPrices['discount'] = $reviewBlock->getDiscount();
        $actualPrices['shipping_excl_tax'] = $reviewBlock->getShippingExclTax();
        $actualPrices['shipping_incl_tax'] = $reviewBlock->getShippingInclTax();
        $actualPrices['tax'] = $reviewBlock->getTax();
        $actualPrices['grand_total_excl_tax'] = $reviewBlock->getGrandTotalExclTax();
        $actualPrices['grand_total_incl_tax'] = $reviewBlock->getGrandTotalInclTax();

        return $actualPrices;
    }

    /**
     * Get order totals.
     *
     * @param $actualPrices
     * @return array
     */
    public function getOrderTotals($actualPrices)
    {
        $viewBlock = $this->customerOrderView->getOrderViewBlock();
        $actualPrices['subtotal_excl_tax'] = null;
        $actualPrices['subtotal_incl_tax'] = $viewBlock->getSubtotal();
        $actualPrices['discount'] = $viewBlock->getDiscount();
        $actualPrices['shipping_excl_tax'] = $viewBlock->getShippingExclTax();
        $actualPrices['shipping_incl_tax'] = $viewBlock->getShippingInclTax();
        $actualPrices['tax'] = $viewBlock->getTax();
        $actualPrices['grand_total_excl_tax'] = $viewBlock->getGrandTotal();
        $actualPrices['grand_total_incl_tax'] = $viewBlock->getGrandTotalInclTax();

        return $actualPrices;
    }
}
