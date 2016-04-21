<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;

/**
 * Checks that prices excl tax on order review and customer order pages are equal to specified in dataset.
 */
class AssertTaxCalculationAfterCheckoutDownloadableExcludingTax extends
 AbstractAssertTaxCalculationAfterCheckoutDownloadable
{
    /**
     * Constraint severeness
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
        $actualPrices['subtotal_excl_tax'] = $reviewBlock->getSubtotal();
        $actualPrices['subtotal_incl_tax'] = null;
        $actualPrices['discount'] = $reviewBlock->getDiscount();
        $actualPrices['shipping_excl_tax'] = $reviewBlock->getShippingExclTax();
        $actualPrices['shipping_incl_tax'] = $reviewBlock->getShippingInclTax();
        $actualPrices['tax'] = $reviewBlock->getTax();
        $actualPrices['grand_total_excl_tax'] = $reviewBlock->getGrandTotal();
        $actualPrices['grand_total_incl_tax'] = null;

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
        $actualPrices['subtotal_excl_tax'] = $viewBlock->getSubtotal();
        $actualPrices['subtotal_incl_tax'] = null;

        $actualPrices['discount'] = $viewBlock->getDiscount();
        $actualPrices['shipping_excl_tax'] = $viewBlock->getShippingExclTax();
        $actualPrices['shipping_incl_tax'] = $viewBlock->getShippingInclTax();
        $actualPrices['tax'] = $viewBlock->getTax();
        $actualPrices['grand_total_excl_tax'] = $viewBlock->getGrandTotal();
        $actualPrices['grand_total_incl_tax'] = null;

        return $actualPrices;
    }
}
