<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\InjectableFixture;

/**
 * Class AssertRelatedProductsSection
 * Assert that product is displayed in related products section
 */
class AssertRelatedProductsSection extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'middle';
    /* end tags */

    /**
     * Assert that product is displayed in related products section
     *
     * @param Browser $browser
     * @param CatalogProductSimple $product
     * @param InjectableFixture[] $relatedProducts
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function processAssert(
        Browser $browser,
        CatalogProductSimple $product,
        array $relatedProducts,
        CatalogProductView $catalogProductView
    ) {
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        foreach ($relatedProducts as $relatedProduct) {
            \PHPUnit_Framework_Assert::assertTrue(
                $catalogProductView->getRelatedProductBlock()->isRelatedProductVisible($relatedProduct->getName()),
                'Product \'' . $relatedProduct->getName() . '\' is absent in related products.'
            );
        }
    }

    /**
     * Text success product is displayed in related products section
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is displayed in related products section.';
    }
}
