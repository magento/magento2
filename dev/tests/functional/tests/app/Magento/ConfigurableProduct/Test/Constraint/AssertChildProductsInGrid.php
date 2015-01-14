<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductInjectable;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertChildProductsInGrid
 * Assert that child products generated during configurable product are present in products grid
 */
class AssertChildProductsInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Default status visibility on child products
     */
    const NOT_VISIBLE_INDIVIDUALLY = 'Not Visible Individually';

    /**
     * Assert that child products generated during configurable product are present in products grid
     *
     * @param CatalogProductIndex $productGrid
     * @param ConfigurableProductInjectable $product
     * @return void
     */
    public function processAssert(CatalogProductIndex $productGrid, ConfigurableProductInjectable $product)
    {
        $configurableAttributesData = $product->getConfigurableAttributesData();
        $productType = $product->getIsVirtual() === "Yes" ? 'Virtual Product' : 'Simple Product';
        $errors = [];

        $productGrid->open();
        foreach ($configurableAttributesData['matrix'] as $variation) {
            $filter = [
                'name' => $variation['name'],
                'type' => $productType,
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

        \PHPUnit_Framework_Assert::assertEmpty($errors, implode($errors, ' '));
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
