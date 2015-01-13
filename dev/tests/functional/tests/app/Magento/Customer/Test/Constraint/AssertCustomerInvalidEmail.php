<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexNew;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerInvalidEmail
 *
 */
class AssertCustomerInvalidEmail extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'middle';
    /* end tags */

    const ERROR_EMAIL_MESSAGE = 'Please correct this email address: "%email%".';

    /**
     * Assert that error message "Please correct this email address: "%email%"." is displayed
     * after customer with invalid email save
     *
     * @param CustomerInjectable $customer
     * @param CustomerIndexNew $pageCustomerIndexNew
     * @return void
     */
    public function processAssert(CustomerInjectable $customer, CustomerIndexNew $pageCustomerIndexNew)
    {
        $expectMessage = str_replace('%email%', $customer->getEmail(), self::ERROR_EMAIL_MESSAGE);
        $actualMessage = $pageCustomerIndexNew->getMessagesBlock()->getErrorMessages();

        \PHPUnit_Framework_Assert::assertEquals(
            $expectMessage,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . $expectMessage
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text success display error message
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert that error message is displayed.';
    }
}
