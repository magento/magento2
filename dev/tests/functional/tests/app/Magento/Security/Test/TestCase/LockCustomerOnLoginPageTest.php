<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Config\Test\Fixture\ConfigData;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Configure maximum login failures to lockout customer.
 *
 * Steps:
 * 1. Open Magento customer login page.
 * 2. Enter incorrect password specified number of times.
 * 3. "Invalid login or password." appears after each login attempt.
 * 4. Perform all assertions.
 *
 * @ZephyrId MAGETWO-49519
 */
class LockCustomerOnLoginPageTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * CustomerAccountLogin page.
     *
     * @var CustomerAccountLogin
     */
    protected $customerAccountLogin;

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Preparing pages for test.
     *
     * @param CustomerAccountLogin $customerAccountLogin
     * @return void
     */
    public function __inject(
        CustomerAccountLogin $customerAccountLogin
    ) {
        $this->customerAccountLogin = $customerAccountLogin;
    }

    /**
     * Run Lock customer on login page test.
     *
     * @param Customer $initialCustomer
     * @param int $attempts
     * @param FixtureFactory $fixtureFactory
     * @param $incorrectPassword
     * @param string $configData
     * @return void
     */
    public function test(
        Customer $initialCustomer,
        $attempts,
        FixtureFactory $fixtureFactory,
        $incorrectPassword,
        $configData = null
    ) {
        $this->configData = $configData;

        // Preconditions
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $initialCustomer->persist();
        $incorrectCustomer = $fixtureFactory->createByCode(
            'customer',
            ['data' => ['email' => $initialCustomer->getEmail(), 'password' => $incorrectPassword]]
        );

        // Steps
        for ($i = 0; $i < $attempts; $i++) {
            $this->customerAccountLogin->open();
            $this->customerAccountLogin->getLoginBlock()->fill($incorrectCustomer);
            $this->customerAccountLogin->getLoginBlock()->submit();
        }
    }

    /**
     * Clean data after running test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
