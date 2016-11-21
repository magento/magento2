<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;

/**
 * Assert Products Qty and Stock Status In Admin Panel.
 */
class AssertProductsQtyAndStockStatusInAdminPanel extends AbstractConstraint
{
    /**
     * Assert products qty and stock status in admin panel.
     *
     * @param CatalogProductEdit $catalogProductEdit
     * @param array $products
     * @param array $expectedQty
     * @param array $expectedStockStatus
     * @return void
     */
    public function processAssert(
        CatalogProductEdit $catalogProductEdit,
        array $products,
        array $expectedQty,
        array $expectedStockStatus
    ) {
        $actualQtyAndStockStatus = [];
        for ($i = 0; $i < count($products); $i++) {
            $catalogProductEdit->open(['id' => $products[$i]->getId()]);
            $productData = $catalogProductEdit->getProductForm()->getData($products[$i]);

            $expectedQtyAndStockStatus = [
                'qty' => $expectedQty[$i],
                'stock_status' => $expectedStockStatus[$i]
            ];

            $actualQtyAndStockStatus['qty'] = $productData['quantity_and_stock_status']['qty'];
            $actualQtyAndStockStatus['stock_status'] =
                strtolower($productData['quantity_and_stock_status']['is_in_stock']);

            \PHPUnit_Framework_Assert::assertEquals(
                $actualQtyAndStockStatus,
                $expectedQtyAndStockStatus,
                'Expected and actual products qty and status are not equal.'
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
        return 'Expected and actual products qty and status are equal.';
    }
}
