<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertNoInvoiceButton
 * Assert no Invoice button the order grid
 */
class AssertNoInvoiceButton extends AbstractConstraint
{
    /**
     * Assert no Invoice button the order grid
     *
     * @param SalesOrderView $orderView
     * @param OrderIndex $orderIndex
     * @param OrderInjectable $order
     * @return void
     */
    public function processAssert(SalesOrderView $orderView, OrderIndex $orderIndex, OrderInjectable $order)
    {
        $orderIndex->open();
        $orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        \PHPUnit_Framework_Assert::assertFalse(
            $orderView->getPageActions()->isActionButtonVisible('Invoice'),
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
