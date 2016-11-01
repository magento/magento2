<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductStatusIsOutOfStock
 * Assert that status of product is out of stock.
 */
class AssertProductStatusIsOutOfStock extends AbstractAssertForm
{
    /**
     * Out of stock message.
     */
    const OUT_OF_STOCK_MESSAGE = 'OUT OF STOCK';

    /**
     * Product view block on frontend page.
     *
     * @var \Magento\Catalog\Test\Block\Product\View
     */
    protected $productView;

    /**
     * Product fixture.
     *
     * @var FixtureInterface
     */
    protected $product;

    /**
     * Assert that status of product is out of stock.
     *
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        FixtureInterface $product
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        \PHPUnit_Framework_Assert::assertEquals(
            self::OUT_OF_STOCK_MESSAGE,
            $catalogProductView->getStockStatusBlock()->getOutOfStockStatus()->getText(),
            'Stock status of product is not "Out of stock"'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Stock status of product is "Out of stock"';
    }
}
