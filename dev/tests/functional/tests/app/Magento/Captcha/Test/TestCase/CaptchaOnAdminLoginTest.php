<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\User\Test\Fixture\User;
use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Captcha\Test\Constraint\AssertCaptchaFieldOnBackend;

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
    protected $adminAuth;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * Injection data.
     *
     * @param AdminAuthLogin $adminAuth
     * @param TestStepFactory $stepFactory
     * @param AssertCaptchaFieldOnBackend $assertCaptcha
     * @return void
     */
    public function __inject(
        AdminAuthLogin $adminAuth,
        TestStepFactory $stepFactory,
        AssertCaptchaFieldOnBackend $assertCaptcha
    ) {
        $this->stepFactory = $stepFactory;
        $this->assertCaptcha = $assertCaptcha;
        $this->adminAuth = $adminAuth;
    }

    /**
     * Create category
     *
     * @param User $customAdmin
     * @param null|string $configData
     * @return void
     */
    public function test(
        User $customAdmin,
        $configData
    ) {
        $this->configData = $configData;
        $customAdmin->persist();

        //Preconditions
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        if ($customAdmin->getCaptcha() !== null) {
            $_ENV['captcha'] = $customAdmin->getCaptcha();
        }

        $this->adminAuth->open();
        $this->adminAuth->getLoginBlock()->fill($customAdmin);
        $this->assertCaptcha->processAssert($this->adminAuth);
        $this->adminAuth->getLoginBlock()->submit();
    }

    /**
     * Set default configuration.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
