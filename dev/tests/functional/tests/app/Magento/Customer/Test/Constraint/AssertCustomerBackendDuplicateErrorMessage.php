<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Asserts duplicate error message on saving backend customer.
 */
class AssertCustomerBackendDuplicateErrorMessage extends AbstractConstraint
{
    /**
     * Error save message text.
     */
    const ERROR_SAVE_MESSAGE = 'A customer with the same email already exists in an associated website.';

    /**
     * Asserts that error message is displayed while creating customer with the same email.
     *
     * @param CustomerIndex $customerIndexPage
     * @return void
     */
    public function processAssert(CustomerIndex $customerIndexPage)
    {
        $actualMessage = $customerIndexPage->getMessagesBlock()->getErrorMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::ERROR_SAVE_MESSAGE,
            $actualMessage,
            'Wrong error message is displayed.'
            . "\nExpected: " . self::ERROR_SAVE_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert that error duplicated message is displayed.';
    }
}
