<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\CustomerAccountForgotPassword;

/**
 * Assert that customer forgot password message is present on customer account forgot password page.
 */
class AssertCustomerResetPasswordFailed extends AbstractConstraint
{
    const TOO_MANY_RESET_REQUESTS_MESSAGE =
        'Too many password reset requests. Please wait and try again or contact hello@example.com.';

    /**
     * Assert that customer forgot password message is present on customer account forgot password page.
     *
     * @param CustomerAccountForgotPassword $customerForgotPassword
     * @return void
     */
    public function processAssert(CustomerAccountForgotPassword $customerForgotPassword)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::TOO_MANY_RESET_REQUESTS_MESSAGE,
            $customerForgotPassword->getMessagesBlock()->getErrorMessage(),
            'Wrong customer reset password failed message is displayed.'
        );
    }

    /**
     * Returns success message if equals to expected message.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer forgot password message is present on customer account forgot password page.';
    }
}
