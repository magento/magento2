<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that status is correct on order page in Admin.
 */
class AssertOrderStatusIsCorrect extends AbstractConstraint
{
    /**
     * Assert that status is correct on order page in Admin.
     *
     * @param string $status
     * @param OrderInjectable $order
     * @param OrderIndex $salesOrder
     * @param SalesOrderView $salesOrderView
     * @param string|null $statusToCheck
     * @return void
     */
    public function processAssert(
        $status,
        OrderInjectable $order,
        OrderIndex $salesOrder,
        SalesOrderView $salesOrderView,
        $statusToCheck = null
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        $orderStatus = $statusToCheck == null ? $status : $statusToCheck;

        \PHPUnit_Framework_Assert::assertEquals(
            $salesOrderView->getOrderForm()->getOrderInfoBlock()->getOrderStatus(),
            $orderStatus
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order status is correct.';
    }
}
