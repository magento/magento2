<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);
=======
>>>>>>> upstream/2.2-develop

namespace Magento\Reports\Test\Constraint;

use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Reports\Test\Page\Adminhtml\OrderedProductsReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
<<<<<<< HEAD
 * Assert product name, sku and qty in Ordered Products report.
=======
 * Assert product name, sku and qty in Ordered Products report
 *
>>>>>>> upstream/2.2-develop
 */
class AssertOrderedProductReportForConfigurableProduct extends AbstractConstraint
{
    /**
<<<<<<< HEAD
     * Assert product name, sku and qty in Ordered Products report.
=======
     * Assert product name, sku and qty in Ordered Products report
>>>>>>> upstream/2.2-develop
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

        \PHPUnit\Framework\Assert::assertContains(
=======
        \PHPUnit_Framework_Assert::assertContains(
>>>>>>> upstream/2.2-develop
            $simpleChildSku[0],
            $filters,
            'Ordered simple product sku is not present in the Reports grid'
        );
    }

    /**
<<<<<<< HEAD
     * Returns a string representation of the object.
=======
     * Returns a string representation of the object
>>>>>>> upstream/2.2-develop
     *
     * @return string
     */
    public function toString()
    {
        return 'Child  product sku is present on the Ordered Products report grid';
    }
}
