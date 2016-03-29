<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderStatus;
use Magento\Sales\Test\Page\Adminhtml\OrderStatusIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderStatusNotAssigned
 * Assert that order status with status code from fixture have empty "State Code and Title" value
 */
class AssertOrderStatusNotAssigned extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that order status with status code from fixture have empty "State Code and Title" value
     *
     * @param OrderStatus $orderStatus
     * @param OrderStatusIndex $orderStatusIndex
     * @return void
     */
    public function processAssert(OrderStatus $orderStatus, OrderStatusIndex $orderStatusIndex)
    {
        $statusLabel = $orderStatus->getLabel();
        \PHPUnit_Framework_Assert::assertFalse(
            $orderStatusIndex->open()->getOrderStatusGrid()->isRowVisible(
                ['label' => $statusLabel, 'state' => $orderStatus->getState()]
            ),
            "Order status $statusLabel is assigned to state."
        );
    }

    /**
     * Return string representation of object
     *
     * @return string
     */
    public function toString()
    {
        return 'Order status with status code from fixture have empty "State Code and Title" value.';
    }
}
