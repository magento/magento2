<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that after refunding full amount 'Credit Memo' is not available any more
 */
class AssertNoCreditMemoButton extends AbstractConstraint
{
    /**
     * Assert that after refunding full amount 'Credit Memo' is not available any more
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
        \PHPUnit_Framework_Assert::assertFalse(
            $salesOrderView->getPageActions()->isActionButtonVisible('Credit Memo'),
            'Credit memo button is present on order view page.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Credit memo button is absent on order view page.';
    }
}
