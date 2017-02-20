<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Captcha\Test\Constraint\AssertCaptchaFieldOnStorefront;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountLogin;

/**
 * Check CAPTCHA on Storefront Login Page.
 *
 * Preconditions:
 * 1. Enable CAPTCHA for customer.
 *
 * Test Flow:
 * 1. Open storefront login form.
 * 2. Log in using captcha.
 *
 * @group Captcha
 * @ZephyrId MAGETWO-43603
 */
class CaptchaOnStoreFrontLoginTest extends Injectable
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
     * CmsIndex page.
     *
     * @var CmsIndex
     */
    private $cmsIndex;

    /**
     * CustomerAccountLogin page.
     *
     * @var CustomerAccountLogin
     */
    private $customerAccountLogin;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * Injection data.
     *
     * @param CmsIndex $cmsIndex
     * @param TestStepFactory $stepFactory
     * @param AssertCaptchaFieldOnStorefront $assertCaptcha
     * @param CustomerAccountLogin $customerAccountLogin
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        TestStepFactory $stepFactory,
        AssertCaptchaFieldOnStorefront $assertCaptcha,
        CustomerAccountLogin $customerAccountLogin
    ) {
        $this->stepFactory = $stepFactory;
        $this->assertCaptcha = $assertCaptcha;
        $this->cmsIndex = $cmsIndex;
        $this->customerAccountLogin = $customerAccountLogin;
    }

    /**
     * Test creation for customer login with captcha on storefront.
     *
     * @param Customer $customer
     * @param null|string $configData
     * @return void
     */
    public function test(
        Customer $customer,
        $configData
    ) {
        $this->configData = $configData;

        // Preconditions
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customer->persist();

        $this->cmsIndex->open();
        $this->cmsIndex->getLinksBlock()->openLink('Sign In');
        $this->cmsIndex->getCmsPageBlock()->waitPageInit();
        $this->assertCaptcha->processAssert($this->customerAccountLogin);
        $this->customerAccountLogin->getLoginBlockWithCaptcha()->getCaptchaReloadButton()->click();
        $this->customerAccountLogin->getLoginBlockWithCaptcha()->login($customer);
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
