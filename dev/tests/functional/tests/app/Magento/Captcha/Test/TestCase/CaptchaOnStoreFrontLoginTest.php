<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\TestCase;

use Magento\Captcha\Test\Constraint\AssertCaptchaFieldOnStorefront;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

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
     * @var AssertCaptchaFieldOnStorefront
     */
    private $assertCaptcha;

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
     * @param TestStepFactory $stepFactory
     * @param AssertCaptchaFieldOnStorefront $assertCaptcha
     * @param CustomerAccountLogin $customerAccountLogin
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        TestStepFactory $stepFactory,
        AssertCaptchaFieldOnStorefront $assertCaptcha,
        CustomerAccountLogin $customerAccountLogin,
        FixtureFactory $fixtureFactory
    ) {
        $this->stepFactory = $stepFactory;
        $this->assertCaptcha = $assertCaptcha;
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

        $this->customerAccountLogin->open();
        $this->assertCaptcha->processAssert($this->customerAccountLogin);
        $this->customerAccountLogin->getLoginBlockWithCaptcha()->reloadCaptcha();
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
