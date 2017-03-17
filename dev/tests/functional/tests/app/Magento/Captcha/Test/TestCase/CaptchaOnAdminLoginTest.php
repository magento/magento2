<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\TestCase;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\User\Test\Fixture\User;
use Magento\Captcha\Test\Constraint\AssertCaptchaFieldOnBackend;
use Magento\Captcha\Test\Page\Captcha\AdminAuthLoginWithCaptcha;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEdit;

/**
 * Check CAPTCHA on Admin Login Page.
 *
 * Preconditions:
 * 1. Enable CAPTCHA for admin.
 *
 * Test Flow:
 * 1. Open backend login form.
 * 2. Log in using captcha.
 * 3. Perform asserts.
 *
 * @group Captcha
 * @ZephyrId MAGETWO-43639
 */
class CaptchaOnAdminLoginTest extends Injectable
{
    /**
     * Admin login page.
     *
     * @var AdminAuthLoginWithCaptcha
     */
    protected $adminAuthWithCaptcha;

    /**
     * System configuration page.
     *
     * @var SystemConfigEdit
     */
    private $systemConfigEditPage;

    /**
     * Injection data.
     *
     * @param SystemConfigEdit $systemConfigEditPage
     * @return void
     */
    public function __inject(SystemConfigEdit $systemConfigEditPage)
    {
        $this->systemConfigEditPage = $systemConfigEditPage;
    }

    /**
     * Log in user to Admin.
     *
     * @param AdminAuthLoginWithCaptcha $adminAuthWithCaptcha
     * @param TestStepFactory $stepFactory
     * @param AssertCaptchaFieldOnBackend $assertCaptcha
     * @param User $customAdmin
     * @param AdminAuthLogin $adminAuthLogin
     * @param string $configData
     * @return void
     */
    public function test(
        AdminAuthLoginWithCaptcha $adminAuthWithCaptcha,
        TestStepFactory $stepFactory,
        AssertCaptchaFieldOnBackend $assertCaptcha,
        User $customAdmin,
        AdminAuthLogin $adminAuthLogin,
        $configData
    ) {
        $customAdmin->persist();

        // Preconditions
        $stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();
        $adminAuthLogin->open();
        $adminAuthWithCaptcha->getLoginBlockWithCaptcha()->fill($customAdmin);
        $assertCaptcha->processAssert($adminAuthWithCaptcha);
        $adminAuthWithCaptcha->getLoginBlockWithCaptcha()->submit();
    }

    /**
     * Set default configuration.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->systemConfigEditPage->open();
        $this->systemConfigEditPage->getForm()
            ->getGroup('admin', 'captcha')->setValue('admin', 'captcha', 'enable', 'No');
        $this->systemConfigEditPage->getPageActions()->save();
    }
}
