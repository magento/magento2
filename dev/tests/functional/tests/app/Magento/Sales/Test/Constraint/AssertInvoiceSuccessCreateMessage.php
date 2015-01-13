<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Sales\Test\Page\Adminhtml\OrderView;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertInvoiceSuccessCreateMessage
 * Assert success invoice create message is displayed on order view page
 */
class AssertInvoiceSuccessCreateMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Text value to be checked
     */
    const SUCCESS_CREATE_MESSAGE = 'The invoice has been created.';

    /**
     * Assert that success message present after create invoice
     *
     * @param OrderView $orderView
     * @return void
     */
    public function processAssert(OrderView $orderView)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_CREATE_MESSAGE,
            $orderView->getMessagesBlock()->getSuccessMessages()
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
