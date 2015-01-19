<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductInGrid
 * Assert that product is present in products grid.
 */
class AssertProductInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Product fixture
     *
     * @var FixtureInterface $product
     */
    protected $product;

    /**
     * Assert that product is present in products grid and can be found by sku, type, status and attribute set.
     *
     * @param FixtureInterface $product
     * @param CatalogProductIndex $productGrid
     * @return void
     */
    public function processAssert(FixtureInterface $product, CatalogProductIndex $productGrid)
    {
        $this->product = $product;
        $productGrid->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $productGrid->getProductGrid()->isRowVisible($this->prepareFilter()),
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
        $productStatus = ($this->product->getStatus() === null || $this->product->getStatus() === 'Product online')
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
     * Get product type
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
