<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that all products are in stock.
 */
class AssertProductsInStock extends AbstractConstraint
{
    /**
     * Assert that In Stock status is displayed for products.
     *
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param AssertProductInStock $assertProductInStock
     * @param array $products
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        AssertProductInStock $assertProductInStock,
        array $products
    ) {
        foreach ($products as $product) {
            $assertProductInStock->processAssert($catalogProductView, $browser, $product);
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'In stock control is visible for each product.';
    }
}
