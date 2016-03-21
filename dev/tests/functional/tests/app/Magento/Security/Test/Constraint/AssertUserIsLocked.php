<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
    const USER_ACCOUNT_DISABLED_MESSAGE = 'account is temporarily disabled';

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
        \PHPUnit_Framework_Assert::assertContains(
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
