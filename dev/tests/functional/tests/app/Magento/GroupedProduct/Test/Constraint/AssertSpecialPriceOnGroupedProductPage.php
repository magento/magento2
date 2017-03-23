<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductSpecialPriceOnProductPage;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\GroupedProduct\Test\Fixture\GroupedProduct;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Class AssertSpecialPriceOnGroupedProductPage
 */
class AssertSpecialPriceOnGroupedProductPage extends AbstractAssertPriceOnGroupedProductPage
{
    /**
     * Format error message
     *
     * @var string
     */
    protected $errorMessage = 'This "%s" product\'s special price on product page NOT equals passed from fixture.';

    /**
     * Successful message
     *
     * @var string
     */
    protected $successfulMessage = 'Special price on grouped product page equals passed from fixture.';

    /**
     * Assert that displayed grouped price on grouped product page equals passed from fixture
     *
     * @param CatalogProductView $catalogProductView
     * @param GroupedProduct $product
     * @param AssertProductSpecialPriceOnProductPage $specialPrice
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        GroupedProduct $product,
        AssertProductSpecialPriceOnProductPage $specialPrice,
        BrowserInterface $browser
    ) {
        $this->processAssertPrice($product, $catalogProductView, $specialPrice, $browser);
    }
}
