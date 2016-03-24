<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\AdminAuthLogin;

/**
 * Assert that user reset password failed message is present on user login page.
 */
class AssertUserPasswordResetFailed extends AbstractConstraint
{
    const TOO_MANY_RESET_REQUESTS_MESSAGE =
        'Too many password reset requests. Please wait and try again or contact hello@example.com.';

    /**
     * Assert that user reset password failed message is present on user login page.
     *
     * @param AdminAuthLogin $adminAuth
     * @return void
     */
    public function processAssert(AdminAuthLogin $adminAuth)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            self::TOO_MANY_RESET_REQUESTS_MESSAGE,
            $adminAuth->getMessagesBlock()->getErrorMessage(),
            'Wrong user reset password failed message is displayed.'
        );
    }

    /**
     * Returns success message if equals to expected message.
     *
     * @return string
     */
    public function toString()
    {
        return 'User reset password failed message is present on user login page.';
    }
}
