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
 * Class AssertUnholdButton
 * Assert that 'Unhold' button present on page
 */
class AssertUnholdButton extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that 'Unhold' button present on order page
     *
     * @param OrderIndex $orderIndex
     * @param OrderView $orderView
     * @param OrderInjectable $order
     * @return void
     */
    public function processAssert(OrderIndex $orderIndex, OrderView $orderView, OrderInjectable $order)
    {
        $orderIndex->open();
        $orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        \PHPUnit_Framework_Assert::assertTrue(
            $orderView->getPageActions()->isActionButtonVisible('Unhold'),
            'Button "Unhold" is absent on order page.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Button "Unhold" is present on order page.';
    }
}
