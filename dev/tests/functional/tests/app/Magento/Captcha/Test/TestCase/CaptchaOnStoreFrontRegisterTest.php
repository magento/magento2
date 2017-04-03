<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\TestCase;

use Magento\Captcha\Test\Constraint\AssertCaptchaFieldOnRegisterForm;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountCreate;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Preconditions:
 * 1. Enable captcha for customer.
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
     * Assert captcha on storefront account register page.
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
     * @return void
     */
    public function __inject(
        TestStepFactory $stepFactory,
        AssertCaptchaFieldOnRegisterForm $assertCaptcha,
        CustomerAccountCreate $customerAccount
    ) {
        $this->stepFactory = $stepFactory;
        $this->assertCaptcha = $assertCaptcha;
        $this->customerAccountCreate = $customerAccount;
    }

    /**
     * Test creation for customer register with captcha on storefront.
     *
     * @param Customer $customer
     * @param string $configData
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
        $this->customerAccountCreate->getRegisterForm()->reloadCaptcha();
        $this->customerAccountCreate->getRegisterForm()->registerCustomer($customer);
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
