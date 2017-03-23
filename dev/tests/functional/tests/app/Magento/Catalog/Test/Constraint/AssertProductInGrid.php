<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that product is present in products grid.
 */
class AssertProductInGrid extends AbstractConstraint
{
    /**
     * Product fixture.
     *
     * @var FixtureInterface $product
     */
    protected $product;

    /**
     * Assert that product is present in products grid and can be found by sku, type, status and attribute set.
     *
     * @param FixtureInterface $product
     * @param CatalogProductIndex $productIndex
     * @return void
     */
    public function processAssert(FixtureInterface $product, CatalogProductIndex $productIndex)
    {
        $this->product = $product;
        $productIndex->open();
        $productIndex->getProductGrid()->resetFilter();
        \PHPUnit_Framework_Assert::assertTrue(
            $productIndex->getProductGrid()->isRowVisible($this->prepareFilter()),
            'Product \'' . $this->product->getName() . '\' is absent in Products grid.'
        );
    }

    /**
     * Prepare filter for product grid.
     *
     * @return array
     */
    protected function prepareFilter()
    {
        $productStatus = ($this->product->getStatus() === null || $this->product->getStatus() === 'Yes')
            ? 'Enabled'
            : 'Disabled';
        $filter = [
            'type' => $this->getProductType(),
            'sku' => $this->product->getSku(),
            'status' => $productStatus,
        ];
        if ($this->product->hasData('attribute_set_id')) {
            $filter['set_name'] = $this->product->getAttributeSetId();
        }

        return $filter;
    }

    /**
     * Get product type.
     *
     * @return string
     */
    protected function getProductType()
    {
        $config = $this->product->getDataConfig();

        return ucfirst($config['type_id']) . ' Product';
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is present in products grid.';
    }
}
