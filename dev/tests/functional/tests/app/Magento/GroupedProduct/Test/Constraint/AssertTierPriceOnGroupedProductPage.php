<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductTierPriceOnProductPage;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\GroupedProduct\Test\Fixture\GroupedProduct;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Class AssertTierPriceOnGroupedProductPage
 * Assert that displayed grouped price on grouped product page equals passed from fixture
 */
class AssertTierPriceOnGroupedProductPage extends AbstractAssertPriceOnGroupedProductPage
{
    /**
     * Format error message
     *
     * @var string
     */
    protected $errorMessage = 'For "%s" Product tier price on product page is not correct.';

    /**
     * Successful message
     *
     * @var string
     */
    protected $successfulMessage = 'Tier price is displayed on the grouped product page.';

    /**
     * Assert that displayed grouped price on grouped product page equals passed from fixture
     *
     * @param CatalogProductView $catalogProductView
     * @param GroupedProduct $product
     * @param AssertProductTierPriceOnProductPage $tierPrice
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        GroupedProduct $product,
        AssertProductTierPriceOnProductPage $tierPrice,
        BrowserInterface $browser
    ) {
        $this->processAssertPrice($product, $catalogProductView, $tierPrice, $browser, 'Tier');
    }
}
