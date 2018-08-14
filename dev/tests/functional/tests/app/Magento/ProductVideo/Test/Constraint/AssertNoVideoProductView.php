<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert that video is absent on product page.
 */
class AssertNoVideoProductView extends AbstractConstraint
{
    /**
     * Assert that video is absent on product page on Store front.
     *
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param InjectableFixture $product
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        InjectableFixture $product
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        \PHPUnit_Framework_Assert::assertFalse(
            $catalogProductView->getViewBlock()->isVideoVisible(),
            'Product video is displayed on product view when it should not.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'No product video is displayed on product view.';
    }
}
