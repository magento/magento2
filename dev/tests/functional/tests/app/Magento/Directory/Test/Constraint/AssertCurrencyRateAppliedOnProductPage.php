<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert currency rate applied on product page.
 */
class AssertCurrencyRateAppliedOnProductPage extends AbstractConstraint
{
    /**
     * Assert currency rate applied on product page.
     *
     * @param BrowserInterface $browser
     * @param InjectableFixture $product
     * @param CatalogProductView $view
     * @param string $basePrice
     */
    public function processAssert(
        BrowserInterface $browser,
        InjectableFixture $product,
        CatalogProductView $view,
        $basePrice
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        \PHPUnit_Framework_Assert::assertEquals(
            $basePrice,
            $view->getViewBlock()->getPriceBlock()->getPrice(''),
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
        return "Currency rate has been applied correctly on Product page.";
    }
}
