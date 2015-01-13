<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerSuccessDeleteMessage
 */
class AssertCustomerSuccessDeleteMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const DELETE_MESSAGE = 'You deleted the customer.';

    /**
     * Asserts that actual delete message equals expected
     *
     * @param CustomerIndex $customerIndexPage
     * @return void
     */
    public function processAssert(CustomerIndex $customerIndexPage)
    {
        $actualMessage = $customerIndexPage->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            self::DELETE_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::DELETE_MESSAGE
            . "\nActual: " . $actualMessage
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
