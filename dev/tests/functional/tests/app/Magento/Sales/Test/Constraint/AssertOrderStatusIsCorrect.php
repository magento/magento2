<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertOrderStatusIsCorrect
 * Assert that status is correct on order page in backend (same with value of orderStatus variable)
 */
class AssertOrderStatusIsCorrect extends AbstractConstraint
{
    /**
     * Assert that status is correct on order page in backend (same with value of orderStatus variable)
     *
     * @param string $status
     * @param string $orderId
     * @param OrderIndex $salesOrder
     * @param SalesOrderView $salesOrderView
     * @param string|null $statusToCheck
     * @return void
     */
    public function processAssert(
        $status,
        $orderId,
        OrderIndex $salesOrder,
        SalesOrderView $salesOrderView,
        $statusToCheck = null
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $orderId]);
        $orderStatus = $statusToCheck == null ? $status : $statusToCheck;

        \PHPUnit_Framework_Assert::assertEquals(
            $salesOrderView->getOrderForm()->getOrderInfoBlock()->getOrderStatus(),
            $orderStatus
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Order status is correct.';
    }
}
