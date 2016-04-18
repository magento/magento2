<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * Assert that video is not displayed in admin product grid.
 */
class AssertProductNoImageInGrid extends AbstractConstraint
{
    /**
     * Assert that video is not displayed in admin panel.
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
            strpos($src, '/placeholder/') !== false,
            'Product image is displayed in product grid when it should not'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product image is not displayed in product grid.';
    }
}
