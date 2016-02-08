<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountCreate;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertPasswordLengthErrorMessage
 */
class AssertPasswordLengthErrorMessage extends AbstractConstraint
{
    const PASSWORD_LENGTH_ERROR_MESSAGE = 'Please enter a password with at least 8 characters.';
    /**
     * Assert that appropriate message is displayed on "Create New Customer Account" page(frontend) if password length
     * is below 8 characters.
     *
     * @param CustomerAccountCreate $registerPage
     * @return void
     */
    public function processAssert(CustomerAccountCreate $registerPage)
    {
        $errorMessage = $registerPage->getMessagesBlock()->getErrorMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::PASSWORD_LENGTH_ERROR_MESSAGE,
            $errorMessage,
            'The messages are not equal.'
        );
    }

    /**
     * Assert that displayed error message is correct
     *
     * @return string
     */
    public function toString()
    {
        return 'Password too short message is present on customer registration page.';
    }
}
