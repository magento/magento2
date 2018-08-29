<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that order is present in Orders grid.
 */
class AssertOrderInOrdersGrid extends AbstractConstraint
{
    /**
     * Assert that order with fixture data is present in Sales -> Orders Grid.
     *
     * @param OrderInjectable $order
     * @param OrderIndex $orderIndex
     * @param string|null $status [optional]
     * @param string $orderId [optional]
     * @return void
     */
    public function processAssert(OrderInjectable $order, OrderIndex $orderIndex, $status = null, $orderId = '')
    {
        $orderIndex->open();
        $this->assert($order, $orderIndex, $status, $orderId);
    }

    /**
     * Process assert.
     *
     * @param OrderInjectable $order
     * @param OrderIndex $orderIndex
     * @param string $status
     * @param string $orderId [optional]
     * @return void
     */
    public function assert(OrderInjectable $order, OrderIndex $orderIndex, $status, $orderId = '')
    {
        $filter = [
            'id' => $order->hasData('id') ? $order->getId() : $orderId,
            'status' => $status
        ];
        $errorMessage = implode(', ', $filter);
        \PHPUnit\Framework\Assert::assertTrue(
            $orderIndex->getSalesOrderGrid()->isRowVisible(array_filter($filter)),
            'Order with following data \'' . $errorMessage . '\' is absent in Orders grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sales order is present in sales orders grid.';
    }
}
