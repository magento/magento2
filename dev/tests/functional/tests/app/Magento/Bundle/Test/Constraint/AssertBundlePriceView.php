<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertBundlePriceView
 */
class AssertBundlePriceView extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that displayed price view for bundle product on product page equals passed from fixture.
     *
     * @param CatalogProductView $catalogProductView
     * @param Browser $browser
     * @param BundleProduct $product
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        Browser $browser,
        BundleProduct $product
    ) {
        //Open product view page
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        //Process assertions
        $this->assertPrice($product, $catalogProductView);
    }

    /**
     * Assert prices on the product view Page
     *
     * @param BundleProduct $product
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    protected function assertPrice(BundleProduct $product, CatalogProductView $catalogProductView)
    {
        $priceData = $product->getDataFieldConfig('price')['source']->getPreset();
        $priceBlock = $catalogProductView->getViewBlock()->getPriceBlock();

        $priceLow = ($product->getPriceView() == 'Price Range')
            ? $priceBlock->getPriceFrom()
            : $priceBlock->getRegularPrice();

        \PHPUnit_Framework_Assert::assertEquals(
            $priceData['price_from'],
            $priceLow,
            'Bundle price From on product view page is not correct.'
        );

        if ($product->getPriceView() == 'Price Range') {
            \PHPUnit_Framework_Assert::assertEquals(
                $priceData['price_to'],
                $priceBlock->getPriceTo(),
                'Bundle price To on product view page is not correct.'
            );
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Bundle price on product view page is not correct.';
    }
}
