<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

class AssertProductHasImageInGrid extends AbstractConstraint
{
    /**
     * Assert that product image is present in grid.
     *
     * @param CatalogProductIndex $productGrid
     * @param InjectableFixture $product
     * @return void
     */
    public function processAssert(
        CatalogProductIndex $productGrid,
        InjectableFixture $product
    ) {
        $filter = ['sku' => $product->getSku()];
        $productGrid->open();
        $productGrid->getProductGrid()->search($filter);
        $src = $productGrid->getProductGrid()->getBaseImageSource();
        \PHPUnit_Framework_Assert::assertTrue(
            strpos($src, '/placeholder/') === false,
            'Product image is not present in product grid when it should be'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product image is displayed in product grid.';
    }
}
