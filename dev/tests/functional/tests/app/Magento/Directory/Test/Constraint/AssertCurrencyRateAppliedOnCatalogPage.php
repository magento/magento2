<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\CurrencySymbol\Test\Fixture\CurrencySymbolEntity;

/**
 * Assert currency rate applied on catalog page.
 */
class AssertCurrencyRateAppliedOnCatalogPage extends AbstractConstraint
{
    /**
     * Assert currency rate applied on catalog page.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param CatalogProductSimple $product
     * @param CurrencySymbolEntity $currencySymbol
     * @param string $basePrice
     * @param string $convertedPrice
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        CatalogProductSimple $product,
        CurrencySymbolEntity $currencySymbol,
        $basePrice,
        $convertedPrice
    ) {
        $categoryName = $product->getCategoryIds()[0];
        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);
        $priceBlock = $catalogCategoryView->getListProductBlock()->getProductItem($product)->getPriceBlock();
        $actualPrice = $priceBlock->getPrice('');

        \PHPUnit_Framework_Assert::assertEquals(
            $basePrice,
            $actualPrice,
            'Wrong price is displayed on Category page.'
        );

        $cmsIndex->getCurrencyBlock()->switchCurrency($currencySymbol);
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);
        $actualPrice = $priceBlock->getPrice('');

        \PHPUnit_Framework_Assert::assertEquals(
            $convertedPrice,
            $actualPrice,
            'Wrong price is displayed on Category page.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Currency rate has been applied correctly on Catalog page.";
    }
}
