<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\User\Test\Fixture\User;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUserWrongCredentialsMessage
 */
class AssertUserWrongCredentialsMessage extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const INVALID_CREDENTIALS_MESSAGE = 'Please correct the user name or password.';

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
            self::INVALID_CREDENTIALS_MESSAGE,
            $adminAuth->getMessagesBlock()->getErrorMessages(),
            'Message "' . self::INVALID_CREDENTIALS_MESSAGE . '" is not visible.'
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
