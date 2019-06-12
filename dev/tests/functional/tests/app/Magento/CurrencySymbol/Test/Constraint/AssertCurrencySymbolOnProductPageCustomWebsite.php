<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Test\Constraint;

use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that correct currency symbol displayed on Product Page on Custom Website.
 */
class AssertCurrencySymbolOnProductPageCustomWebsite extends AbstractConstraint
{
    /**
     * Assert that correct currency symbol displayed on Product Page on Custom Website.
     *
     * @param InjectableFixture $product,
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param array $currencySymbol
     * @return void
     */
    public function processAssert(
        InjectableFixture $product,
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        array $currencySymbol = []
    ) {
        $website = $product->getDataFieldConfig('website_ids')['source']->getWebsites()[0];
        $url = $_ENV['app_frontend_url'] . 'websites/' . $website->getCode() . '/' . $product->getUrlKey() . '.html';
        $browser->open($url);
        $priceBlock = $catalogProductView->getViewBlock()->getPriceBlock();
        $symbolOnPage = $priceBlock->getCurrencySymbol();

        \PHPUnit\Framework\Assert::assertEquals(
            $currencySymbol['customWebsite'],
            $symbolOnPage,
            'Wrong Currency Symbol is displayed on Product page on Custom website.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Correct Currency Symbol displayed on Product page on Custom website.";
    }
}
