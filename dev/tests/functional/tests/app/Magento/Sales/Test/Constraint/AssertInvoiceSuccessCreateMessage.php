<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert success invoice create message is displayed on order view page
 */
class AssertInvoiceSuccessCreateMessage extends AbstractConstraint
{
    /**
     * Text value to be checked
     */
    const SUCCESS_CREATE_MESSAGE = 'The invoice has been created.';

    /**
     * Assert that success message present after create invoice
     *
     * @param SalesOrderView $salesOrderView
     * @return void
     */
    public function processAssert(SalesOrderView $salesOrderView)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_CREATE_MESSAGE,
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
        return 'Success invoice create message is present.';
    }
}
