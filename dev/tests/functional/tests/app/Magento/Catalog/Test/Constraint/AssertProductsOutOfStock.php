<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that all products have Out of Stock status.
 */
class AssertProductsOutOfStock extends AbstractConstraint
{
    /**
     * Assert that all products have Out of Stock status.
     *
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param AssertProductOutOfStock $assertProductOutOfStock
     * @param array $products
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        AssertProductOutOfStock $assertProductOutOfStock,
        array $products
    ) {
        foreach ($products as $product) {
            $assertProductOutOfStock->processAssert($catalogProductView, $browser, $product);
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'All products have Out of Stock status.';
    }
}
