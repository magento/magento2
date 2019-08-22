<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Admin login page with Captcha.
     *
     * @var AdminAuthLoginWithCaptcha
     */
    private $adminAuthWithCaptcha;

    /**
     * System configuration page.
     *
     * @var SystemConfigEdit
     */
    private $systemConfigEditPage;

    /**
     * Login page for Admin.
     *
     * @var AdminAuthLogin
     */
    private $adminAuthLogin;

    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Assert captcha on backend login page.
     *
     * @var AssertCaptchaFieldOnBackend
     */
    private $assertCaptcha;

    /**
     * Injection data.
     *
     * @param SystemConfigEdit $systemConfigEditPage
     * @param AdminAuthLoginWithCaptcha $adminAuthWithCaptcha
     * @param TestStepFactory $stepFactory
     * @param AdminAuthLogin $adminAuthLogin
     * @param AssertCaptchaFieldOnBackend $assertCaptcha
     * @return void
     */
    public function __inject(
        SystemConfigEdit $systemConfigEditPage,
        AdminAuthLoginWithCaptcha $adminAuthWithCaptcha,
        TestStepFactory $stepFactory,
        AdminAuthLogin $adminAuthLogin,
        AssertCaptchaFieldOnBackend $assertCaptcha
    ) {
        $this->systemConfigEditPage = $systemConfigEditPage;
        $this->adminAuthWithCaptcha = $adminAuthWithCaptcha;
        $this->stepFactory = $stepFactory;
        $this->adminAuthLogin = $adminAuthLogin;
        $this->assertCaptcha = $assertCaptcha;
    }

    /**
     * Log in user to Admin.
     *
     * @param User $customAdmin
     * @param string $configData
     * @return void
     */
    public function test(User $customAdmin, $configData)
    {
        $customAdmin->persist();

        // Preconditions
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();
        $this->adminAuthLogin->open();
        $this->adminAuthWithCaptcha->getLoginBlockWithCaptcha()->fill($customAdmin);
        $this->assertCaptcha->processAssert($this->adminAuthWithCaptcha);
        $this->adminAuthWithCaptcha->getLoginBlockWithCaptcha()->submit();
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
