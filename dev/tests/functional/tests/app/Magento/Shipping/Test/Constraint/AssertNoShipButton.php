<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert no Ship button in the order grid
 */
class AssertNoShipButton extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert no Ship button in the order grid
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
            $salesOrderView->getPageActions()->isActionButtonVisible('Ship'),
            'Ship button is present on order view page.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Ship button is absent on order view page.';
    }
}
