<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Fixture\FixtureInterface;

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
     * @param FixtureInterface $product
     * @param array $actualPrices
     * @return array
     */
    public function getCategoryPrices(FixtureInterface $product, $actualPrices)
    {
        $priceBlock = $this->catalogCategoryView->getListProductBlock()->getProductItem($product)->getPriceBlock();
        $actualPrices['category_price_excl_tax'] = $priceBlock->getPriceExcludingTax();
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
        $actualPrices['product_view_price_excl_tax'] = $viewBlock->getPriceBlock()->getPriceExcludingTax();
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
