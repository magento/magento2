<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that after cancel sales order successful message appears
 */
class AssertOrderCancelSuccessMessage extends AbstractConstraint
{
    /**
     * Message displayed after cancel sales order
     */
    const SUCCESS_CANCEL_MESSAGE = 'You canceled the order.';

    /**
     * Assert that after cancel sales order successful message appears
     *
     * @param SalesOrderView $salesOrderView
     * @return void
     */
    public function processAssert(SalesOrderView $salesOrderView)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_CANCEL_MESSAGE,
            $salesOrderView->getMessagesBlock()->getSuccessMessage()
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Sales order success cancel message is present.';
    }
}
