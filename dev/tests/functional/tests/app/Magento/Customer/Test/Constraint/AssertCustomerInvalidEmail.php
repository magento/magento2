<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndexNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerInvalidEmail
 *
 */
class AssertCustomerInvalidEmail extends AbstractConstraint
{
    const ERROR_EMAIL_MESSAGE = '"Email" is not a valid hostname.';

    /**
     * Assert that error message "Please correct this email address: "%email%"." is displayed
     * after customer with invalid email save
     *
     * @param CustomerIndexNew $pageCustomerIndexNew
     * @return void
     */
    public function processAssert(CustomerIndexNew $pageCustomerIndexNew)
    {
        $expectMessage = self::ERROR_EMAIL_MESSAGE;
        $actualMessage = $pageCustomerIndexNew->getMessagesBlock()->getErrorMessage();

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
