<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that child products sku generated from parent sku.
 */
class AssertChildProductsGeneratedSku extends AbstractConstraint
{
    /**
     * Assert that child products sku generated from parent sku.
     *
     * @param CatalogProductIndex $productGrid
     * @param ConfigurableProduct $product
     * @return void
     */
    public function processAssert(CatalogProductIndex $productGrid, ConfigurableProduct $product)
    {
        $configurableAttributesData = $product->getConfigurableAttributesData();
        $productGrid->open();
        foreach ($configurableAttributesData['matrix'] as $variation) {
            $filter = ['name' => $variation['name']];
            $productGrid->getProductGrid()->search($filter);
            $itemId = $productGrid->getProductGrid()->getFirstItemId();
            \PHPUnit\Framework\Assert::assertContains(
                $product->getSku(),
                $productGrid->getProductGrid()->getColumnValue($itemId, 'SKU'),
                'Product sku is not generated from parent sku.'
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
        return 'Child products sku is generated from parent sku.';
    }
}
