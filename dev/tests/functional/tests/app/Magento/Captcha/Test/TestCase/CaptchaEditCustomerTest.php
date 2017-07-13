<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountEdit;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Check captcha and lockout customer on the account edit page.
 *
 * Preconditions:
 * 1. Enable CAPTCHA for customer.
 * 2. Set Maximum Login Failures.
 * 3. Create customer.
 *
 * Test Flow:
 * 1. Log in to Store Front.
 * 2. Open customer account edit page.
 * 2. Update email with incorrect password 3 or more times.
 * 3. Update email with incorrect password and captcha(111) 3 or more times.
 * 5. Perform asserts.
 *
 * @group Captcha
 * @ZephyrId MAGETWO-49049
 */
class CaptchaEditCustomerTest extends Injectable
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
     * Customer Edit page.
     *
     * @var CustomerAccountEdit
     */
    private $customerAccountEdit;

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
     * @param CustomerAccountEdit $customerAccountEdit
     * @return void
     */
    public function __inject(
        TestStepFactory $stepFactory,
        FixtureFactory $fixtureFactory,
        CustomerAccountEdit $customerAccountEdit
    ) {
        $this->stepFactory = $stepFactory;
        $this->fixtureFactory = $fixtureFactory;
        $this->customerAccountEdit = $customerAccountEdit;
    }

    /**
     * Test for checking captcha on the customer account edit page and customer is locked.
     *
     * @param Customer $customer
     * @param Customer $initCustomer
     * @param string $configData
     * @param string $captcha
     * @param int $attempts
     * @return void
     */
    public function test(
        Customer $customer,
        Customer $initCustomer,
        $configData,
        $captcha,
        $attempts
    ) {
        $this->configData = $configData;

        // Preconditions
        $customer->persist();
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $this->stepFactory->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();

        // Steps
        $this->customerAccountEdit->getAccountMenuBlock()->openMenuItem('Account Information');

        // Update email with incorrect password $attempts times.
        $this->customerEdit($initCustomer, $attempts);

        // Update email with incorrect password and captcha $attempts + 1 times.
        $data = $initCustomer->getData();
        $data['captcha'] = $captcha;
        $data['group_id'] = [];

        /** @var Customer $initCustomer */
        $initCustomer = $this->fixtureFactory->createByCode('customer', ['data' => $data]);
        //Add + 1 to attempts to get over maximum attempts count.
        $this->customerEdit($initCustomer, $attempts + 1);
    }

    /**
     * Update email with incorrect password $attempts times.
     *
     * @param Customer $customer
     * @param int $attempts
     * @return void
     */
    private function customerEdit(Customer $customer, $attempts)
    {
        $accountInfoForm = $this->customerAccountEdit->getAccountInfoForm();
        for ($i = 0; $i < $attempts; $i++) {
            $accountInfoForm->setChangeEmail(true);
            $accountInfoForm->fill($customer);
            $accountInfoForm->submit();
        }
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
