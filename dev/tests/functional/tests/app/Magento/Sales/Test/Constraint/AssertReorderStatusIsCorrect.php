<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderView;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertReorderStatusIsCorrect
 * Assert that status is correct on order page in backend
 */
class AssertReorderStatusIsCorrect extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that status is correct on order page in backend (same with value of orderStatus variable)
     *
     * @param string $previousOrderStatus
     * @param OrderInjectable $order
     * @param OrderIndex $salesOrder
     * @param OrderView $salesOrderView
     * @return void
     */
    public function processAssert(
        $previousOrderStatus,
        OrderInjectable $order,
        OrderIndex $salesOrder,
        OrderView $salesOrderView
    ) {
        $salesOrder->open();
        $salesOrder->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);

        \PHPUnit_Framework_Assert::assertEquals(
            $salesOrderView->getOrderForm()->getOrderInfoBlock()->getOrderStatus(),
            $previousOrderStatus,
            'Order status is incorrect on order page in backend.'
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
