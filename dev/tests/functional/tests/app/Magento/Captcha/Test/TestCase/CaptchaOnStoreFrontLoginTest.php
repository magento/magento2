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
use Magento\Captcha\Test\Page\CustomerAccountLogin;
use Magento\Mtf\Fixture\FixtureFactory;

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
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

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
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        TestStepFactory $stepFactory,
        AssertCaptchaFieldOnStorefront $assertCaptcha,
        CustomerAccountLogin $customerAccountLogin,
        FixtureFactory $fixtureFactory
    ) {
        $this->stepFactory = $stepFactory;
        $this->assertCaptcha = $assertCaptcha;
        $this->cmsIndex = $cmsIndex;
        $this->customerAccountLogin = $customerAccountLogin;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Test creation for customer login with captcha on storefront.
     *
     * @param Customer $customer
     * @param null|string $configData
     * @param string $captcha
     * @return void
     */
    public function test(
        Customer $customer,
        $configData,
        $captcha
    ) {
        $this->configData = $configData;

        // Preconditions
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customer->persist();

        $customerData = $customer->getData();
        $customerData['group_id'] = [
            'customerGroup' => $customer->getDataFieldConfig('group_id')['source']->getCustomerGroup()
        ];
        $customerData['captcha'] = $captcha;

        $customer = $this->fixtureFactory->createByCode('customer', ['data' => $customerData]);

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
