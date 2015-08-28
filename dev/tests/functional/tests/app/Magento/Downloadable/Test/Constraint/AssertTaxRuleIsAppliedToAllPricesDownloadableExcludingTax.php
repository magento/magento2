<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Constraint;

use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Checks that product prices excl tax on category, product and cart pages are equal to specified in dataset.
 */
class AssertTaxRuleIsAppliedToAllPricesDownloadableExcludingTax extends
 AbstractAssertTaxRuleIsAppliedToAllPricesDownloadable
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
        $actualPrices['category_price'] = $priceBlock->getPrice();
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
        $priceBlock = $this->catalogProductView->getViewBlock()->getPriceBlock();
        $actualPrices['product_view_price'] = $priceBlock->getPrice();
        $actualPrices['product_view_price_excl_tax'] = $priceBlock->getPriceExcludingTax();
        $actualPrices['product_view_price_incl_tax'] = $priceBlock->getPriceIncludingTax();

        return $actualPrices;
    }

    /**
     * Get totals.
     *
     * @param array $actualPrices
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
