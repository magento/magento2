<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;

/**
 * Assert Product Qty In Admin Panel.
 */
class AssertProductQtyInAdminPanel extends AbstractConstraint
{
    /**
     * Text value for checking Stock Availability.
     */
    const STOCK_AVAILABILITY = 'out of stock';

    /**
     * Assert that Out of Stock status is displayed on product page.
     *
     * @param CatalogProductEdit $catalogProductEdit
     * @param array $products
     * @param array $expectedQty
     * @return void
     */
    public function processAssert(
        CatalogProductEdit $catalogProductEdit,
        array $products,
        array $expectedQty
    ) {
        $actualQty = [];
        foreach ($products as $product) {
            $catalogProductEdit->open(['id' => $product->getId()]);
            $actualQty[] = $catalogProductEdit->getProductForm()->getData($product)['quantity_and_stock_status']['qty'];
        }

        \PHPUnit_Framework_Assert::assertEquals(
            $expectedQty,
            $actualQty,
            '"Expected and actual products qty are not equal."'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Expected and actual products qty are equal.';
    }
}
