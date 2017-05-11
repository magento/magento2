<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;

/**
 * Assert that customer's Shopping Cart section on Order Create backend page is empty.
 */
class AssertCartSectionIsEmptyOnBackendOrderPage extends AbstractConstraint
{
    /**
     * Assert that customer's Shopping Cart section on Order Create backend page is empty.
     *
     * @param OrderCreateIndex $orderCreateIndex
     * @return void
     */
    public function processAssert(OrderCreateIndex $orderCreateIndex)
    {
        $orderCreateIndex->open();
        $backendOrderSidebarBlock = $orderCreateIndex->getBackendOrderSidebarBlock()->noItemsInCartCheck();
        \PHPUnit_Framework_Assert::assertTrue(
            $backendOrderSidebarBlock,
            "Customer's Shopping Cart section on Order Create backend page is not empty."
        );
    }

    /**
     * Success assert message that customer's Shopping Cart section on Order Create backend page is empty.
     *
     * @return string
     */
    public function toString()
    {
        return "Customer's Shopping Cart section on Order Create backend page is empty.";
    }
}
