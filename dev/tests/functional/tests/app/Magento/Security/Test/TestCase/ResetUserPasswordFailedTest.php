<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\ObjectManager;
use Magento\User\Test\Fixture\User;
use Magento\Security\Test\Page\UserAccountForgotPassword;

/**
 * Preconditions:
 * 1. Create admin user.
 *
 * Steps:
 * 1. Open Magento admin user reset password page.
 * 2. Perform password reset action specified number of times.
 * 3. Password reset failed message appears on each password reset attempt starting the second one.
 * 4. Perform all assertions.
 *
 * @ZephyrId MAGETWO-49028
 */
class ResetUserPasswordFailedTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
    /* end tags */

    /**
     * Admin User Forgot Password page.
     *
     * @var UserAccountForgotPassword
     */
    protected $userAccountForgotPassword;

    protected $systemConfig;

    /**
     * Preparing pages for test.
     *
     * @param UserAccountForgotPassword $userAccountForgotPassword
     * @return void
     */
    public function __inject(
        UserAccountForgotPassword $userAccountForgotPassword
    ) {
        $this->userAccountForgotPassword = $userAccountForgotPassword;
    }

    /**
     * Run reset user password failed test.
     * @param User $customAdmin
     * @param int $attempts
     * @return void
     */
    public function test(User $customAdmin, $attempts)
    {
        // Steps
        $customAdmin->persist();
        for ($i = 0; $i < $attempts; $i++) {
            $this->userAccountForgotPassword->open();
            $this->userAccountForgotPassword->getForgotPasswordForm()->fill($customAdmin);
            $this->userAccountForgotPassword->getForgotPasswordForm()->submit();
        }
    }
}
