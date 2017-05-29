<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that after create sales order successful message appears
 */
class AssertOrderSuccessCreateMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Message displayed after created sales order
     */
    const SUCCESS_MESSAGE = "You created the order.";

    /**
     * Assert that after create sales order successful message appears
     *
     * @param SalesOrderView $salesOrderView
     * @return void
     */
    public function processAssert(SalesOrderView $salesOrderView)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $salesOrderView->getMessagesBlock()->getSuccessMessage(),
            'Wrong success message is displayed.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Sales order success created message is present.';
    }
}
