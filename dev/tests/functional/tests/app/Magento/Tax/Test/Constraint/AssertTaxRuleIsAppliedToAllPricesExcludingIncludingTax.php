<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Checks that prices excl and incl tax on category, product and cart pages are equal to specified in dataset
 */
class AssertTaxRuleIsAppliedToAllPricesExcludingIncludingTax extends
 AbstractAssertTaxRuleIsAppliedToAllPrices
{
    /**
     * Constraint severeness.
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Get prices on category page.
     *
     * @param $productName
     * @param array $actualPrices
     * @return array
     */
    public function getCategoryPrices($productName, $actualPrices)
    {
        $priceBlock = $this->catalogCategoryView->getListProductBlock()->getProductPriceBlock($productName);
        $actualPrices['category_price_excl_tax'] = $priceBlock->getPriceExcludingTax();
        $actualPrices['category_price_incl_tax'] = $priceBlock->getPriceIncludingTax();

        return $actualPrices;
    }

    /**
     * Get product view prices.
     *
     * @param array $actualPrices
     * @return array
     */
    public function getProductPagePrices($actualPrices)
    {
        $viewBlock = $this->catalogProductView->getViewBlock();
        $actualPrices['product_view_price_excl_tax'] = $viewBlock->getProductPriceExcludingTax();
        $actualPrices['product_view_price_incl_tax'] = $viewBlock->getProductPriceIncludingTax();

        return $actualPrices;
    }

    /**
     * Get totals.
     *
     * @param $actualPrices
     * @return array
     */
    public function getTotals($actualPrices)
    {
        $totalsBlock = $this->checkoutCart->getTotalsBlock();
        $actualPrices['subtotal_excl_tax'] = $totalsBlock->getSubtotalExcludingTax();
        $actualPrices['subtotal_incl_tax'] = $totalsBlock->getSubtotalIncludingTax();
        $actualPrices['discount'] = $totalsBlock->getDiscount();
        $actualPrices['shipping_excl_tax'] = $totalsBlock->getShippingPrice();
        $actualPrices['shipping_incl_tax'] = $totalsBlock->getShippingPriceInclTax();
        $actualPrices['tax'] = $totalsBlock->getTax();
        $actualPrices['grand_total_excl_tax'] = $totalsBlock->getGrandTotalExcludingTax();
        $actualPrices['grand_total_incl_tax'] = $totalsBlock->getGrandTotalIncludingTax();

        return $actualPrices;
    }
}
