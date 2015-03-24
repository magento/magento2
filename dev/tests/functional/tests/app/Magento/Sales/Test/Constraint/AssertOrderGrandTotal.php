<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Order Grand Total is correct on order page in backend
 */
class AssertOrderGrandTotal extends AbstractConstraint
{
    /**
     * Assert that Order Grand Total is correct on order page in backend
     *
     * @param SalesOrderView $salesOrderView
     * @param string $orderId
     * @param OrderIndex $salesOrder
     * @param string $grandTotal
     * @return void
     */
    public function processAssert(
        SalesOrderView $salesOrderView,
        OrderIndex $salesOrder,
        $orderId,
        $grandTotal
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);

        \PHPUnit_Framework_Assert::assertEquals(
            $grandTotal,
            $salesOrderView->getOrderTotalsBlock()->getGrandTotal(),
            'Grand Total price does not equal to price from data set.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Grand Total price equals to price from data set.';
    }
}
