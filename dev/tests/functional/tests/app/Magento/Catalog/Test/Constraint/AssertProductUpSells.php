<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert that product is displayed in up-sell section.
 */
class AssertProductUpSells extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'middle';
    /* end tags */

    /**
     * Assert that products are displayed in up-sell section.
     *
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param CatalogProductSimple $product
     * @param InjectableFixture[]|null $promotedProducts
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        CatalogProductSimple $product,
        array $promotedProducts = null
    ) {
        if (!$promotedProducts) {
            $promotedProducts = $product->hasData('up_sell_products')
                ? $product->getDataFieldConfig('up_sell_products')['source']->getProducts()
                : [];
        }

        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        foreach ($promotedProducts as $promotedProduct) {
            \PHPUnit_Framework_Assert::assertTrue(
                $catalogProductView->getUpsellBlock()->getProductItem($promotedProduct)->isVisible(),
                'Product \'' . $promotedProduct->getName() . '\' is absent in up-sells products.'
            );
        }
    }

    /**
     * Text success product is displayed in up-sell section.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is displayed in up-sell section.';
    }
}
