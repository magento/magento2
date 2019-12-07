<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogImportExport\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Constraint\AssertProductInGrid;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that products are present in products grid.
 */
class AssertProductsInGrid extends AbstractConstraint
{
    /**
     * Assert that products are present in products grid and can be found by sku, type, status and attribute set.
     *
     * @param CatalogProductIndex $productIndex
     * @param AssertProductInGrid $assertProductInGrid
     * @param array $entities
     * @return void
     */
    public function processAssert(
        CatalogProductIndex $productIndex,
        AssertProductInGrid $assertProductInGrid,
        array $entities
    ) {
        foreach ($entities as $entity) {
            $assertProductInGrid->processAssert($entity, $productIndex);
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Products are present in products grid.';
    }
}
