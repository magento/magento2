<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\TestCase;

use Magento\Customer\Test\Block\Form\Login;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;

/**
 * Preconditions:
 * 1. Enable CAPTCHA for customer.
 * 2. Set Maximum Login Failures.
 * 3. Create customer.
 *
 * Test Flow:
 * 1. Open storefront login form.
 * 2. Log in customer with incorrect password 3 or more times.
 * 3. Log in customer with captcha and incorrect password 3 or more times.
 * 4. Log in customer with captcha and correct password.
 * 5. Perform asserts.
 *
 * @group Captcha
 * @ZephyrId MAGETWO-49048
 */
class CaptchaAndLockoutCustomerTest extends Injectable
{
    /**
     * Step factory.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * CustomerAccountLogin page.
     *
     * @var CustomerAccountLogin
     */
    private $customerAccountLogin;

    /**
     * Customer Edit page
     *
     * @var CustomerIndexEdit
     */
    private $customerIndexEdit;

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
     * @param FixtureFactory $fixtureFactory
     * @param CustomerAccountLogin $customerAccountLogin
     * @param CustomerIndexEdit $customerIndexEdit
     * @return void
     */
    public function __inject(
        TestStepFactory $stepFactory,
        FixtureFactory $fixtureFactory,
        CustomerAccountLogin $customerAccountLogin,
        CustomerIndexEdit $customerIndexEdit
    ) {
        $this->stepFactory = $stepFactory;
        $this->fixtureFactory = $fixtureFactory;
        $this->customerAccountLogin = $customerAccountLogin;
        $this->customerIndexEdit = $customerIndexEdit;
    }

    /**
     * Test creation for customer login with captcha on storefront.
     *
     * @param Customer $customer
     * @param string $configData
     * @param string $captcha
     * @param string $incorrectPassword
     * @param int $attempts
     * @return void
     */
    public function test(
        Customer $customer,
        $configData,
        $captcha,
        $incorrectPassword,
        $attempts
    ) {
        $this->configData = $configData;

        // Preconditions
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customer->persist();

        $correctData = $customer->getData();
        $correctData['captcha'] = $captcha;
        $correctData['group_id'] = [
            'customerGroup' => $customer->getDataFieldConfig('group_id')['source']->getCustomerGroup()
        ];
        $incorrectData = $correctData;
        $incorrectData['password'] = $incorrectPassword;

        $customer = $this->fixtureFactory->createByCode('customer', ['data' => $incorrectData]);

        // Steps
        $this->customerAccountLogin->open();

        // Fill incorrect password 3 or more times.
        $this->customerLogin($customer, $this->customerAccountLogin->getLoginBlock(), $attempts);

        // Fill correct captcha and incorrect password 3 or more times.
        $this->customerLogin($customer, $this->customerAccountLogin->getLoginBlockWithCaptcha(), $attempts);

        // Log in customer with correct captcha and correct password.
        $customer = $this->fixtureFactory->createByCode('customer', ['data' => $correctData]);
        $this->customerLogin($customer, $this->customerAccountLogin->getLoginBlockWithCaptcha(), 1);
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

    /**
     * Log in customer $attempts times.
     *
     * @param Customer $customer
     * @param Login $loginForm
     * @param int $attempts
     * @return void
     */
    private function customerLogin(Customer $customer, Login $loginForm, $attempts)
    {
        for ($i = 0; $i < $attempts; $i++) {
            $loginForm->fill($customer);
            $loginForm->submit();
        }
    }
}
