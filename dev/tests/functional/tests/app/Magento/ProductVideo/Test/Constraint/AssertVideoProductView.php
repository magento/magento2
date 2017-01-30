<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Constraint;

use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Assert that video is displayed on product page.
 */
class AssertVideoProductView extends AbstractConstraint
{
    /**
     * Assert that video is displayed on product page on Store front.
     *
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param InjectableFixture $product
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        InjectableFixture $initialProduct
    ) {
        $browser->open($_ENV['app_frontend_url'] . $initialProduct->getUrlKey() . '.html');
        $catalogProductView->getViewBlock()->isGalleryVisible();
        \PHPUnit_Framework_Assert::assertTrue(
            $catalogProductView->getViewBlock()->isGalleryVisible(),
            'Product video is not displayed on product view when it should.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product video is displayed on product view.';
    }
}
