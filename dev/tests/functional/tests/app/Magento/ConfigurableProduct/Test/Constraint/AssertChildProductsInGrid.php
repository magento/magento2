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
 * Class AssertChildProductsInGrid
 * Assert that child products generated during configurable product are present in products grid
 */
class AssertChildProductsInGrid extends AbstractConstraint
{
    /**
     * Default status visibility on child products
     */
    const NOT_VISIBLE_INDIVIDUALLY = 'Not Visible Individually';

    /**
     * Assert that child products generated during configurable product are present in products grid
     *
     * @param CatalogProductIndex $productGrid
     * @param ConfigurableProduct $product
     * @return void
     */
    public function processAssert(CatalogProductIndex $productGrid, ConfigurableProduct $product)
    {
        $configurableAttributesData = $product->getConfigurableAttributesData();
        $errors = [];

        $productGrid->open();
        foreach ($configurableAttributesData['matrix'] as $variation) {
            $filter = [
                'name' => $variation['name'],
                'type' => (isset($variation['weight']) && (int)$variation['weight'] > 0)
                    ? 'Simple Product' : 'Virtual Product',
                'sku' => $variation['sku'],
                'visibility' => self::NOT_VISIBLE_INDIVIDUALLY,
            ];

            if (!$productGrid->getProductGrid()->isRowVisible($filter)) {
                $errors[] = sprintf(
                    'Child product with name: "%s" and sku:"%s" is absent in grid.',
                    $filter['name'],
                    $filter['sku']
                );
            }
        }

        \PHPUnit_Framework_Assert::assertEmpty($errors, implode(' ', $errors));
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Child products generated during configurable product are present in products grid.';
    }
}
