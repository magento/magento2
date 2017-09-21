<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;

/**
 * Assert that customer's Shopping Cart section on Order Create backend page contains products.
 */
class AssertCartSectionWithProductsOnBackendOrderPage extends AbstractConstraint
{
    /**
     * Assert that customer's Shopping Cart section on Order Create backend page contains products.
     *
     * @param OrderCreateIndex $orderCreateIndex
     * @param array $products
     * @return void
     */
    public function processAssert(OrderCreateIndex $orderCreateIndex, array $products)
    {
        $orderCreateIndex->open();
        foreach ($products as $product) {
            $expectedItemNames[] = $product->getName();
        }
        $itemsNames = $orderCreateIndex->getBackendOrderSidebarBlock()->getCartItemsNames();
        \PHPUnit_Framework_Assert::assertEquals(
            sort($expectedItemNames),
            sort($itemsNames),
            "Customer's Shopping Cart section on Order Create backend page doesn't contain correct products."
        );
    }

    /**
     * Success assert that customer's Shopping Cart section on Order Create backend page contains products.
     *
     * @return string
     */
    public function toString()
    {
        return "Customer's Shopping Cart section on Order Create backend page contains correct products.";
    }
}
