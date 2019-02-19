<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\SystemConfigEdit;
use Magento\Mtf\TestCase\Injectable;
use Magento\Security\Test\Page\UserAccountForgotPassword;
use Magento\User\Test\Fixture\User;

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
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Admin User Forgot Password page.
     *
     * @var UserAccountForgotPassword
     */
    protected $userAccountForgotPassword;

    protected $systemConfig;

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * System configuration page.
     *
     * @var SystemConfigEdit
     */
    private $systemConfigEditPage;

    /**
     * Preparing pages for test.
     *
     * @param UserAccountForgotPassword $userAccountForgotPassword
     * @param SystemConfigEdit $systemConfigEditPage
     * @return void
     */
    public function __inject(
        UserAccountForgotPassword $userAccountForgotPassword,
        SystemConfigEdit $systemConfigEditPage
    ) {
        $this->userAccountForgotPassword = $userAccountForgotPassword;
        $this->systemConfigEditPage = $systemConfigEditPage;
    }

    /**
     * Run reset user password failed test.
     * @param User $customAdmin
     * @param int $attempts
     * @param string $configData
     * @return void
     */
    public function test(
        User $customAdmin,
        $attempts,
        $configData = null
    ) {
        $this->configData = $configData;

        // Steps
        $customAdmin->persist();

        // Preconditions
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        $this->systemConfigEditPage->open();
        $this->systemConfigEditPage->getForm()
            ->getGroup('admin', 'captcha')->setValue('admin', 'captcha', 'enable', 'No');
        $this->systemConfigEditPage->getPageActions()->save();

        for ($i = 0; $i < $attempts; $i++) {
            $this->userAccountForgotPassword->open();
            $this->userAccountForgotPassword->getForgotPasswordForm()->fill($customAdmin);
            $this->userAccountForgotPassword->getForgotPasswordForm()->submit();
        }
    }
}
