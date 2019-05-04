<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert no Invoice button the order grid
 */
class AssertNoInvoiceButton extends AbstractConstraint
{
    /**
     * Assert no Invoice button the order grid
     *
     * @param SalesOrderView $salesOrderView
     * @param OrderIndex $orderIndex
     * @param OrderInjectable $order
     * @return void
     */
    public function processAssert(SalesOrderView $salesOrderView, OrderIndex $orderIndex, OrderInjectable $order)
    {
        $orderIndex->open();
        $orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        \PHPUnit\Framework\Assert::assertFalse(
            $salesOrderView->getPageActions()->isActionButtonVisible('Invoice'),
            'Invoice button is present on order view page.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Invoice button is absent on order view page.';
    }
}
