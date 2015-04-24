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
 * Class AssertOrdersInOrdersGrid
 * Assert that orders are present in Orders grid
 */
class AssertOrdersInOrdersGrid extends AbstractConstraint
{
    /**
     * Assert that orders are present in Sales -> Orders Grid
     *
     * @param OrderInjectable[] $orders
     * @param OrderIndex $orderIndex
     * @param array $orderStatuses
     * @param AssertOrderInOrdersGrid $assertOrderInOrdersGrid
     * @return void
     */
    public function processAssert(
        $orders,
        OrderIndex $orderIndex,
        array $orderStatuses,
        AssertOrderInOrdersGrid $assertOrderInOrdersGrid
    ) {
        $orderIndex->open();
        foreach ($orders as $key => $order) {
            $assertOrderInOrdersGrid->assert($order, $orderIndex, $orderStatuses[$key]);
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'All orders are present in sales orders grid.';
    }
}
