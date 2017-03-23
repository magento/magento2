<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\CurrencySymbol\Test\Fixture\CurrencySymbolEntity;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that after applying changes, currency symbol changed on Product Details Page.
 */
class AssertCurrencySymbolOnProductPage extends AbstractConstraint
{
    /**
     * Assert that after applying changes, currency symbol changed on Product Details Page.
     *
     * @param CatalogProductSimple $product
     * @param BrowserInterface $browser
     * @param CmsIndex $cmsIndex
     * @param CatalogProductView $catalogProductView
     * @param CurrencySymbolEntity $currencySymbol
     * @return void
     */
    public function processAssert(
        CatalogProductSimple $product,
        BrowserInterface $browser,
        CmsIndex $cmsIndex,
        CatalogProductView $catalogProductView,
        CurrencySymbolEntity $currencySymbol
    ) {
        $cmsIndex->open();
        $cmsIndex->getCurrencyBlock()->switchCurrency($currencySymbol);
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $price = $catalogProductView->getViewBlock()->getPriceBlock()->getPrice();
        preg_match('`(.*?)\d`', $price, $matches);

        $symbolOnPage = isset($matches[1]) ? $matches[1] : null;
        \PHPUnit_Framework_Assert::assertEquals(
            $currencySymbol->getCustomCurrencySymbol(),
            $symbolOnPage,
            'Wrong Currency Symbol is displayed on Product page.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Currency Symbol has been changed on Product Details page.";
    }
}
