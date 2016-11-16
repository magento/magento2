<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert product stock status.
 */
class AssertProductOutOfStock extends AbstractConstraint
{
    /**
     * Text value for checking Stock Availability.
     */
    const STOCK_AVAILABILITY = 'out of stock';

    /**
     * Browser instance.
     *
     * @var BrowserInterface
     */
    private $browser;

    /**
     * Catalog product page.
     *
     * @var CatalogProductView
     */
    private $catalogProductView;

    /**
     * Assert that Out of Stock status is displayed on product page.
     *
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param FixtureInterface $product
     * @param array $products
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        FixtureInterface $product = null,
        array $products = null
    ) {
        $this->catalogProductView = $catalogProductView;
        $this->browser = $browser;
        if ($product) {
            $this->stockStatusAssertion($product);
        }
        if ($products) {
            foreach ($products as $product) {
                $this->stockStatusAssertion($product);
            }
        }
    }

    /**
     * Assert product stock status.
     *
     * @param FixtureInterface $product
     * @return void
     */
    private function stockStatusAssertion(FixtureInterface $product)
    {
        $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        \PHPUnit_Framework_Assert::assertEquals(
            self::STOCK_AVAILABILITY,
            $this->catalogProductView->getViewBlock()->stockAvailability(),
            'Control \'' . self::STOCK_AVAILABILITY . '\' is not visible.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Out of stock control is visible.';
    }
}
