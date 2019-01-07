<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Checks that "Add to Cart" button is absent on product page.
 */
class AssertAddToCartButtonAbsentOnProductPage extends AbstractConstraint
{
    /**
     * Assert that "Add to Cart" button is absent on product page.
     *
     * @param BrowserInterface $browser
     * @param InjectableFixture $product
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        InjectableFixture $product,
        CatalogProductView $catalogProductView
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        \PHPUnit\Framework\Assert::assertFalse(
            $catalogProductView->getViewBlock()->isVisibleAddToCardButton(),
            'Button "Add to Cart" is present on product page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Button "Add to Cart" is absent on product page.';
    }
}
