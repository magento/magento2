<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerGroupIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerGroupSuccessDeleteMessage
 */
class AssertCustomerGroupSuccessDeleteMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    const SUCCESS_DELETE_MESSAGE= "The customer group has been deleted.";

    /**
     * Assert that message "The customer group has been deleted." is displayed on Customer Group page.
     *
     * @param CustomerGroupIndex $customerGroupIndex
     * @return void
     */
    public function processAssert(CustomerGroupIndex $customerGroupIndex)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_DELETE_MESSAGE,
            $customerGroupIndex->getMessagesBlock()->getSuccessMessages(),
            'Wrong message is displayed.'
            . "\nExpected: " . self::SUCCESS_DELETE_MESSAGE
            . "\nActual: " . $customerGroupIndex->getMessagesBlock()->getSuccessMessages()
        );
    }

    /**
     * Text success delete message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert that success delete message is displayed.';
    }
}
