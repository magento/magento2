<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert that product is not displayed in up-sell section.
 */
class AssertProductAbsentUpSells extends AbstractConstraint
{
    /**
     * Assert that product is not displayed in up-sell section.
     *
     * @param BrowserInterface $browser
     * @param CatalogProductSimple $product
     * @param CatalogProductView $catalogProductView
     * @param InjectableFixture[]|null $promotedProducts
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductSimple $product,
        CatalogProductView $catalogProductView,
        array $promotedProducts = null
    ) {
        if (!$promotedProducts) {
            $promotedProducts = $product->hasData('up_sell_products')
                ? $product->getDataFieldConfig('up_sell_products')['source']->getProducts()
                : [];
        }

        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        foreach ($promotedProducts as $promotedProduct) {
            \PHPUnit_Framework_Assert::assertFalse(
                $catalogProductView->getUpsellBlock()->getProductItem($promotedProduct)->isVisible(),
                'Product \'' . $promotedProduct->getName() . '\' is exist in up-sells products.'
            );
        }
    }

    /**
     * Text success product is not displayed in up-sell section.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is not displayed in up-sell section.';
    }
}
