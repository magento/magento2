<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Block\Product\View;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that displayed tier price on product page equals passed from fixture.
 */
class AssertProductTierPriceOnProductPage extends AbstractConstraint implements AssertPriceOnProductPageInterface
{
    /**
     * Error message
     *
     * @var string
     */
    protected $errorMessage = 'Product tier price on product page is not correct.';

    /**
     * Format price
     *
     * @var int
     */
    protected $priceFormat = 2;

    /**
     * Assertion that tier prices are displayed correctly
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
        // TODO fix initialization url for frontend page
        //Open product view page
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        //Process assertions
        $this->assertPrice($product, $catalogProductView->getViewBlock());
    }

    /**
     * Set $errorMessage for tier price assert
     *
     * @param string $errorMessage
     * @return void
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * Verify product tier price on product view page
     *
     * @param FixtureInterface $product
     * @param View $productViewBlock
     * @return void
     */
    public function assertPrice(FixtureInterface $product, View $productViewBlock)
    {
        $noError = true;
        $match = [];
        $index = 1;
        $tierPrices = $product->getTierPrice();

        foreach ($tierPrices as $tierPrice) {
            $text = $productViewBlock->getTierPrices($index++);
            $noError = (bool)preg_match('#^[^\d]+(\d+)[^\d]+(\d+(?:(?:,\d+)*)+(?:.\d+)*).*#i', $text, $match);
            if (!$noError) {
                break;
            }
            if (count($match) < 2
                && $match[1] != $tierPrice['price_qty']
                || $match[2] !== number_format($tierPrice['price'], $this->priceFormat)
            ) {
                $noError = false;
                break;
            }
        }

        \PHPUnit_Framework_Assert::assertTrue($noError, $this->errorMessage);
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Tier price is displayed on the product page.';
    }
}
