<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 */
class AssertOrderedProductReportForConfigurableProduct extends AbstractConstraint
{
    /**
<<<<<<< HEAD
     * Assert product name, sku and qty in Ordered Products report
=======
     * Assert product name, sku and qty in Ordered Products report.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     *
     * @return string
     */
    public function toString()
    {
        return 'Child  product sku is present on the Ordered Products report grid';
    }
}
