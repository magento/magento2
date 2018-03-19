<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check displayed price view for bundle product on product page.
 */
class AssertBundlePriceView extends AbstractConstraint
{
    /**
     * Assert that displayed price view for bundle product on product page equals passed from fixture.
     *
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param BundleProduct $product
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        BundleProduct $product
    ) {
        //Open product view page
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');

        //Process assertions
        $this->assertPrice($product, $catalogProductView);
    }

    /**
     * Assert prices on the product view Page.
     *
     * @param BundleProduct $product
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    protected function assertPrice(BundleProduct $product, CatalogProductView $catalogProductView)
    {
        $priceData = $product->getDataFieldConfig('price')['source']->getPriceData();
        $priceView = $product->getPriceView();
        $priceBlock = $catalogProductView->getViewBlock()->getPriceBlock();

        if ($product->hasData('special_price')) {
            $priceLow = $priceBlock->getPrice();
        } else {
            $priceLow = ($priceView == 'Price Range') ? $priceBlock->getPriceFrom() : $priceBlock->getPrice();
        }

        \PHPUnit\Framework\Assert::assertEquals(
            $priceData['price_from'],
            $priceLow,
            'Bundle price From on product view page is not correct.'
        );

        if ($priceView == 'Price Range') {
            \PHPUnit\Framework\Assert::assertEquals(
                $priceData['price_to'],
                $priceBlock->getPriceTo(),
                'Bundle price To on product view page is not correct.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Bundle price on product view page is not correct.';
    }
}
