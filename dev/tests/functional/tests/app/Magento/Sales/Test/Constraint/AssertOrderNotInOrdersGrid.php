<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderNotInOrdersGrid
 * Assert that order with fixture data in not more in the Orders grid
 */
class AssertOrderNotInOrdersGrid extends AbstractConstraint
{
    /**
     * Assert that order with fixture data in not more in the Orders grid
     *
     * @param OrderInjectable $order
     * @param OrderIndex $orderIndex
     * @return void
     */
    public function processAssert(OrderInjectable $order, OrderIndex $orderIndex)
    {
        $data = $order->getData();
        $filter = ['id' => $data['id']];
        $orderIndex->open();
        $errorMessage = implode(', ', $filter);
        \PHPUnit_Framework_Assert::assertFalse(
            $orderIndex->getSalesOrderGrid()->isRowVisible($filter),
            'Order with following data \'' . $errorMessage . '\' is present in Orders grid.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Order is absent in sales orders grid.';
    }
}
