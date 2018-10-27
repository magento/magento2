<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Constraint\AbstractConstraint;

class AssertUpdatedProductStatusInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Asserts that the Disabled product is present in the grid after filtering by the product status
     *
     * @param CatalogProductSimple $product
     * @param CatalogProductIndex $catalogProductIndex
     * @return void
     */
    public function processAssert(
        CatalogProductSimple $product,
        CatalogProductIndex $catalogProductIndex
    ) {
        $catalogProductIndex->open();
        $productStatus = ($product->getStatus() === null || $product->getStatus() === 'Yes')
            ? 'Enabled'
            : 'Disabled';
        $filter = ['status' => $productStatus];
        \PHPUnit_Framework_Assert::assertTrue(
            $catalogProductIndex->getProductGrid()->isRowVisible($filter),
            'Product \'' . $product->getName() . '\' is absent in Products grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Products are in grid.';
    }
}
