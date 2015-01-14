<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Checks that prices incl tax on category, product and cart pages are equal to specified in dataset.
 */
class AssertTaxRuleIsAppliedToAllPricesExcludingTax extends AbstractAssertTaxRuleIsAppliedToAllPrices
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
        $actualPrices['category_price_excl_tax'] = $priceBlock->getEffectivePrice();
        $actualPrices['category_price_incl_tax'] = null;

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
        $actualPrices['product_view_price_excl_tax'] = $viewBlock->getPriceBlock()->getEffectivePrice();
        $actualPrices['product_view_price_incl_tax'] = null;

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
        $actualPrices['subtotal_excl_tax'] = $totalsBlock->getSubtotal();
        $actualPrices['subtotal_incl_tax'] = null;
        $actualPrices['discount'] = $totalsBlock->getDiscount();
        $actualPrices['shipping_excl_tax'] = $totalsBlock->getShippingPrice();
        $actualPrices['shipping_incl_tax'] = $totalsBlock->getShippingPriceInclTax();
        $actualPrices['tax'] = $totalsBlock->getTax();
        $actualPrices['grand_total_excl_tax'] = $totalsBlock->getGrandTotal();
        $actualPrices['grand_total_incl_tax'] = null;

        return $actualPrices;
    }
}
