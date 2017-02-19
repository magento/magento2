<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\User\Test\Fixture\User;
use Magento\Captcha\Test\Constraint\AssertCaptchaFieldOnBackend;
use Magento\Captcha\Test\Page\Captcha\AdminAuthLoginWithCaptcha;

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
     * Step factory.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Assert Captcha.
     *
     * @var string
     */
    private $assertCaptcha;

    /**
     * Admin login page.
     *
     * @var AdminAuthLogin
     */
    protected $adminAuthWithCaptcha;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * Injection data.
     *
     * @param AdminAuthLoginWithCaptcha $adminAuthWithCaptcha
     * @param TestStepFactory $stepFactory
     * @param AssertCaptchaFieldOnBackend $assertCaptcha
     * @return void
     */
    public function __inject(
        AdminAuthLoginWithCaptcha $adminAuthWithCaptcha,
        TestStepFactory $stepFactory,
        AssertCaptchaFieldOnBackend $assertCaptcha
    ) {
        $this->stepFactory = $stepFactory;
        $this->adminAuthWithCaptcha = $adminAuthWithCaptcha;
        $this->assertCaptcha = $assertCaptcha;
    }

    /**
     * Login customer in backend.
     *
     * @param User $customAdmin
     * @param $configData
     * @return void
     */
    public function test(
        User $customAdmin,
        $configData
    ) {
        $this->configData = $configData;
        $customAdmin->persist();

        // Preconditions
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        $this->adminAuthWithCaptcha->open();
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
        $configurationPage = \Magento\Mtf\ObjectManagerFactory::getObjectManager()->create(
            \Magento\Backend\Test\Page\Adminhtml\SystemConfigEdit::class
        );

        $configurationPage->open();
        $configurationPage->getForm()
            ->getGroup('admin', 'captcha')->setValue('admin', 'captcha', 'enable', 'No');
        $configurationPage->getPageActions()->save();
    }
}
