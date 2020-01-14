<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Constraint;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUserIsLocked
 */
class AssertUserIsLocked extends AbstractConstraint
{
    const USER_ACCOUNT_DISABLED_MESSAGE = 'The account sign-in was incorrect or your account is disabled temporarily. '
    . 'Please wait and try again later.';

    /**
     * Verify that user account has been locked.
     *
     * @param AdminAuthLogin $adminAuth
     * @return void
     */
    public function processAssert(
        AdminAuthLogin $adminAuth
    ) {
        $ignoreCase = true;
        \PHPUnit\Framework\Assert::assertContains(
            self::USER_ACCOUNT_DISABLED_MESSAGE,
            $adminAuth->getMessagesBlock()->getErrorMessage(),
            'Message "' . self::USER_ACCOUNT_DISABLED_MESSAGE . '" is not visible.',
            $ignoreCase
        );
    }

    /**
     * Assert that displayed error message is correct
     *
     * @return string
     */
    public function toString()
    {
        return 'User account locked message is displayed on user login page.';
    }
}
