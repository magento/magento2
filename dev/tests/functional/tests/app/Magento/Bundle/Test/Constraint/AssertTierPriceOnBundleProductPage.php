<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductTierPriceOnProductPage;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Mtf\Client\Browser;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AssertTierPriceOnBundleProductPage
 */
class AssertTierPriceOnBundleProductPage extends AssertProductTierPriceOnProductPage
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Tier price block
     *
     * @var string
     */
    protected $tierBlock = '.prices.tier.items';

    /**
     * Decimals for price format
     *
     * @var int
     */
    protected $priceFormat = 4;

    /**
     * Assertion that tier prices are displayed correctly
     *
     * @param Browser $browser
     * @param CatalogProductView $catalogProductView
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(Browser $browser, CatalogProductView $catalogProductView, FixtureInterface $product)
    {
        //Open product view page
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $viewBlock = $catalogProductView->getBundleViewBlock();
        $viewBlock->clickCustomize();

        //Process assertions
        $this->assertPrice($product, $viewBlock);
    }
}
