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
 * Assert that order is present in the grid after setting date from and date to in filter on the orders grid page.
 */
class AssertOrderByDateInOrdersGrid extends AbstractConstraint
{
    /**
     * Assert that order with fixture data is present in Sales -> Orders Grid with applied date filters.
     *
     * @param OrderInjectable $order
     * @param OrderIndex $orderIndex
     * @return void
     */
    public function processAssert(OrderInjectable $order, OrderIndex $orderIndex)
    {
        $filter = [
            'id' => $order->getId(),
            'purchase_date_from' => date('m/j/Y', strtotime('-1 year')),
            'purchase_date_to' => date('m/j/Y', strtotime('+1 year'))
        ];
        $orderIndex->open();
        $orderIndex->getSalesOrderGrid()->search($filter);
        \PHPUnit_Framework_Assert::assertTrue(
            $orderIndex->getSalesOrderGrid()->isFirstRowVisible(),
            'Order with following id ' . $order->getId() . ' is absent in Orders grid with applied date.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sales order is present in sales orders grid with applied dates in filter.';
    }
}
