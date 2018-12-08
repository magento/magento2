<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

namespace Magento\Reports\Test\Constraint;

use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Reports\Test\Page\Adminhtml\OrderedProductsReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
<<<<<<< HEAD
 * Assert product name, sku and qty in Ordered Products report
 *
=======
 * Assert product name, sku and qty in Ordered Products report.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
class AssertOrderedProductReportForConfigurableProduct extends AbstractConstraint
{
    /**
<<<<<<< HEAD
     * Assert product name, sku and qty in Ordered Products report
=======
     * Assert product name, sku and qty in Ordered Products report.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @param OrderedProductsReport $orderedProducts
     * @param OrderInjectable $order
     * @return void
     */
    public function processAssert(OrderedProductsReport $orderedProducts, OrderInjectable $order)
    {
        $products = $order->getEntityId()['products'];
        $simpleChildSku = $orderedProducts->getGridBlock()->getOrdersResultsforConfigurableProducts($order);
        $filters = [];
        foreach ($products as $product) {
            /** @var ConfigurableProduct $product */
            if ($product->hasData('configurable_attributes_data')) {
                $matrix = isset($product->getConfigurableAttributesData()['matrix']) ?
                    $product->getConfigurableAttributesData()['matrix'] : [];
                foreach ($matrix as $variation) {
                    $filters[] = $variation['sku'];
                }
            }
        }
<<<<<<< HEAD
        \PHPUnit_Framework_Assert::assertContains(
=======

        \PHPUnit\Framework\Assert::assertContains(
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            $simpleChildSku[0],
            $filters,
            'Ordered simple product sku is not present in the Reports grid'
        );
    }

    /**
<<<<<<< HEAD
     * Returns a string representation of the object
=======
     * Returns a string representation of the object.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @return string
     */
    public function toString()
    {
        return 'Child  product sku is present on the Ordered Products report grid';
    }
}
