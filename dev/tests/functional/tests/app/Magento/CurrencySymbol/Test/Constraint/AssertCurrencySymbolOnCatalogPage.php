<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\CurrencySymbol\Test\Fixture\CurrencySymbolEntity;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCurrencySymbolOnCatalogPage
 * Check that after applying changes, currency symbol changed on Catalog page
 */
class AssertCurrencySymbolOnCatalogPage extends AbstractConstraint
{
    /**
     * Assert that after applying changes, currency symbol changed on Catalog page
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param CatalogProductSimple $product
     * @param CurrencySymbolEntity $currencySymbol
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        CatalogProductSimple $product,
        CurrencySymbolEntity $currencySymbol
    ) {
        $categoryName = $product->getCategoryIds()[0];
        $cmsIndex->open();
        $cmsIndex->getCurrencyBlock()->switchCurrency($currencySymbol);
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);
        $price = $catalogCategoryView->getListProductBlock()->getProductItem($product)->getPriceBlock()->getPrice('');
        preg_match('`(.*?)\d`', $price, $matches);

        $symbolOnPage = isset($matches[1]) ? $matches[1] : null;
        \PHPUnit_Framework_Assert::assertEquals(
            $currencySymbol->getCustomCurrencySymbol(),
            $symbolOnPage,
            'Wrong Currency Symbol is displayed on Category page.'
        );
    }

    /**
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return "Currency Symbol has been changed on Catalog page.";
    }
}
