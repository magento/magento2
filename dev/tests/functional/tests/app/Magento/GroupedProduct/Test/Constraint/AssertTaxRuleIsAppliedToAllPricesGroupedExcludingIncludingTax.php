<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Constraint;

use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Checks that prices excl tax on category, product and cart pages are equal to specified in dataset.
 */
class AssertTaxRuleIsAppliedToAllPricesGroupedExcludingIncludingTax extends
 AbstractAssertTaxRuleIsAppliedToAllPricesOnGroupedProductPage
{
    /**
     * @inheritdoc
     */
    public function getCategoryPrices(FixtureInterface $product, $actualPrices)
    {
        $priceBlock = $this->catalogCategoryView->getListProductBlock()->getProductItem($product)->getPriceBlock();
        $actualPrices['category_price_excl_tax'] = $priceBlock->getPriceExcludingTax();
        $actualPrices['category_price_incl_tax'] = $priceBlock->getPriceIncludingTax();

        return $actualPrices;
    }

    /**
     * @inheritdoc
     */
    public function getGroupedProductPagePrices(FixtureInterface $product, array $actualPrices)
    {
        $associatedProducts = $product->getAssociated();
        /** @var \Magento\GroupedProduct\Test\Block\Catalog\Product\View $groupedProductBlock */
        $this->catalogProductView = $this->catalogProductView->getGroupedProductViewBlock();
        foreach (array_keys($associatedProducts['products']) as $productIndex) {
            //Process assertions
            $this->catalogProductView ->itemPriceProductBlock(++$productIndex);
            $actualPrices['sub_product_view_prices_' . $productIndex] =  $this->getProductPagePrices($actualPrices);
        }
        return $actualPrices;
    }

    /**
     * @inheritdoc
     */
    public function getProductPagePrices($actualPrices)
    {
        $priceBlock = $this->catalogProductView ->getPriceBlock();
        $productPrices['product_view_price_excl_tax'] = $priceBlock->getPriceExcludingTax();
        $productPrices['product_view_price_incl_tax'] = $priceBlock->getPriceIncludingTax();

        return $productPrices;
    }

    /**
     * @inheritdoc
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
