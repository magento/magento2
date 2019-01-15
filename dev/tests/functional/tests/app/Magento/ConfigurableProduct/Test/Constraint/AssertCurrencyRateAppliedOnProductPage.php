<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\CurrencySymbol\Test\Fixture\CurrencySymbolEntity;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert currency rate applied on configurable product page.
 */
class AssertCurrencyRateAppliedOnProductPage extends AbstractConstraint
{
    /**
     * Assert currency rate applied on configurable product page.
     *
     * @param BrowserInterface $browser
     * @param InjectableFixture $product
     * @param CatalogProductView $view
     * @param CmsIndex $cmsIndex
     * @param CurrencySymbolEntity $baseCurrency
     * @param array $configuredPrices
     * @param string $basePrice
     */
    public function processAssert(
        BrowserInterface $browser,
        InjectableFixture $product,
        CatalogProductView $view,
        CmsIndex $cmsIndex,
        CurrencySymbolEntity $baseCurrency,
        array $configuredPrices,
        $basePrice
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $this->assertPrice($view, $basePrice);

        $view->getViewBlock()->configure($product);
        $this->assertPrice($view, $configuredPrices['custom_currency']);

        $cmsIndex->getCurrencyBlock()->switchCurrency($baseCurrency);
        $view->getViewBlock()->configure($product);
        $this->assertPrice($view, $configuredPrices['base_currency']);
    }

    /**
     * Assert price.
     *
     * @param CatalogProductView $view
     * @param string $price
     * @param string $currency [optional]
     */
    public function assertPrice(CatalogProductView $view, $price, $currency = '')
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $price,
            $view->getViewBlock()->getPriceBlock()->getPrice($currency),
            'Wrong price is displayed on Product page.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return "Currency rate has been applied correctly on Configurable Product page.";
    }
}
