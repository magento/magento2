<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Reports\Test\Page\Adminhtml\OrderedProductsReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderedProductResult
 * Assert product name and qty in Ordered Products report
 *
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class AssertOrderedProductResult extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert product name and qty in Ordered Products report
     *
     * @param OrderedProductsReport $orderedProducts
     * @param OrderInjectable $order
     * @return void
     */
    public function processAssert(OrderedProductsReport $orderedProducts, OrderInjectable $order)
    {
        $products = $order->getEntityId()['products'];
        $totalQuantity = $orderedProducts->getGridBlock()->getOrdersResults($order);
        $productQty = [];

        foreach ($totalQuantity as $key => $value) {
            /** @var CatalogProductSimple $product */
            $product = $products[$key];
            $productQty[$key] = $product->getCheckoutData()['qty'];
        }
        \PHPUnit_Framework_Assert::assertEquals($totalQuantity, $productQty);
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Ordered Products result is equals to data from fixture.';
    }
}
