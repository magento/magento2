<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that correct currency symbol displayed on Product Page in Main Website.
 */
class AssertCurrencySymbolOnProductPageMainWebsite extends AbstractConstraint
{
    /**
     * Assert that correct currency symbol displayed on Product Page in Main Website.
     *
     * @param CatalogProductSimple $product
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param array|null $currencySymbol
     * @return void
     */
    public function processAssert(
        CatalogProductSimple $product,
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        array $currencySymbol  = []
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $price = $catalogProductView->getViewBlock()->getPriceBlock()->getPriceWithCurrency();
        preg_match('`(.*?)\d`', $price, $matches);

        $symbolOnPage = isset($matches[1]) ? $matches[1] : null;
        \PHPUnit_Framework_Assert::assertEquals(
            $currencySymbol['mainWebsite'],
            $symbolOnPage,
            'Wrong Currency Symbol is displayed on Product page in the Main Website.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Correct Currency Symbol displayed on Product page in the Main Website.";
    }
}
