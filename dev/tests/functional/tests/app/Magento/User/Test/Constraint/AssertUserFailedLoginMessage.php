<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\User\Test\Fixture\User;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUserFailedLoginMessage
 */
class AssertUserFailedLoginMessage extends AbstractConstraint
{
    const FAILED_LOGIN_MESSAGE = 'You did not sign in correctly or your account is temporarily disabled.';

    /**
     * Verify incorrect credentials message while login to admin
     *
     * @param AdminAuthLogin $adminAuth
     * @param User $customAdmin
     * @return void
     */
    public function processAssert(
        AdminAuthLogin $adminAuth,
        User $customAdmin
    ) {
        $adminAuth->open();
        $adminAuth->getLoginBlock()->fill($customAdmin);
        $adminAuth->getLoginBlock()->submit();

        \PHPUnit_Framework_Assert::assertEquals(
            self::FAILED_LOGIN_MESSAGE,
            $adminAuth->getMessagesBlock()->getErrorMessage(),
            'Message "' . self::FAILED_LOGIN_MESSAGE . '" is not visible.'
        );
    }

    /**
     * Returns error message equals expected message
     *
     * @return string
     */
    public function toString()
    {
        return 'Invalid credentials message was displayed.';
    }
}
