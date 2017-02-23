<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Captcha\Test\Constraint\AssertCaptchaFieldOnRegisterForm;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountCreate;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Preconditions:
 * 1. Enable CAPTCHA for customer.
 *
 * Test Flow:
 * 1. Open storefront account register form.
 * 2. Register customer using captcha.
 *
 * @group Captcha
 * @ZephyrId MAGETWO-43602
 */
class CaptchaOnStoreFrontRegisterTest extends Injectable
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
     * @var AssertCaptchaFieldOnRegisterForm
     */
    private $assertCaptcha;

    /**
     * CustomerAccountCreate page.
     *
     * @var CustomerAccountCreate
     */
    private $customerAccountCreate;

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
     * @param AssertCaptchaFieldOnRegisterForm $assertCaptcha
     * @param CustomerAccountCreate $customerAccount
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        TestStepFactory $stepFactory,
        AssertCaptchaFieldOnRegisterForm $assertCaptcha,
        CustomerAccountCreate $customerAccount,
        FixtureFactory $fixtureFactory
    ) {
        $this->stepFactory = $stepFactory;
        $this->assertCaptcha = $assertCaptcha;
        $this->customerAccountCreate = $customerAccount;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Test creation for customer register with captcha on storefront.
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

        $this->customerAccountCreate->open();
        $this->assertCaptcha->processAssertRegisterForm($this->customerAccountCreate);
        $this->customerAccountCreate->getRegisterFormWithCaptcha()->getCaptchaReloadButton()->click();
        $this->customerAccountCreate->getRegisterFormWithCaptcha()->registerCustomer($customer);
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
