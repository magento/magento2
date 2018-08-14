<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert that product is displayed in related products section.
 */
class AssertProductRelatedProducts extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'middle';
    /* end tags */

    /**
     * Assert that products are displayed in related products section.
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
            $promotedProducts = $product->hasData('related_products')
                ? $product->getDataFieldConfig('related_products')['source']->getProducts()
                : [];
        }

        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        foreach ($promotedProducts as $promotedProduct) {
            \PHPUnit_Framework_Assert::assertTrue(
                $catalogProductView->getRelatedProductBlock()->getProductItem($promotedProduct)->isVisible(),
                'Product \'' . $promotedProduct->getName()
                . '\' is absent in related products \'' . $product->getName() . '\'.'
            );
        }
    }

    /**
     * Text success product is displayed in related products section.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is displayed in related products section.';
    }
}
