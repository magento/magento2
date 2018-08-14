<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductDuplicatedInGrid
 */
class AssertProductDuplicatedInGrid extends AbstractConstraint
{
    /**
     * Assert that duplicated product is found by sku and has correct product type, attribute set,
     * product status disabled and out of stock
     *
     * @param FixtureInterface $product
     * @param CatalogProductIndex $productGrid
     * @return void
     */
    public function processAssert(FixtureInterface $product, CatalogProductIndex $productGrid)
    {
        $config = $product->getDataConfig();
        $filter = [
            'name' => $product->getName(),
            'visibility' => $product->getVisibility(),
            'status' => 'Disabled',
            'sku' => $product->getSku() . '-1',
            'type' => ucfirst($config['create_url_params']['type']) . ' Product',
            'price_to' => number_format($product->getPrice(), 2),
        ];

        $productGrid->open()
            ->getProductGrid()
            ->search($filter);

        $filter['price_to'] = '$' . $filter['price_to'];
        \PHPUnit_Framework_Assert::assertTrue(
            $productGrid->getProductGrid()->isRowVisible($filter, false),
            'Product duplicate is absent in Products grid.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'The product has been successfully found, according to the filters.';
    }
}
