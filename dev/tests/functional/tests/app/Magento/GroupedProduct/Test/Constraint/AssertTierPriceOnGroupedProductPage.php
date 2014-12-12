<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\GroupedProduct\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductTierPriceOnProductPage;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\GroupedProduct\Test\Fixture\GroupedProductInjectable;
use Mtf\Client\Browser;

/**
 * Class AssertTierPriceOnGroupedProductPage
 * Assert that displayed grouped price on grouped product page equals passed from fixture
 */
class AssertTierPriceOnGroupedProductPage extends AbstractAssertPriceOnGroupedProductPage
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
     * @param GroupedProductInjectable $product
     * @param AssertProductTierPriceOnProductPage $tierPrice
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        GroupedProductInjectable $product,
        AssertProductTierPriceOnProductPage $tierPrice,
        Browser $browser
    ) {
        $this->processAssertPrice($product, $catalogProductView, $tierPrice, $browser, 'Tier');
    }
}
