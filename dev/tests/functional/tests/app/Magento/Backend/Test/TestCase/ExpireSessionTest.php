<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Steps:
 *
 * 1. Login to Admin.
 * 2. Create customer if needed.
 * 3. Apply configuration settings.
 * 4. Wait for session to expire.
 * 5. Perform asserts.
 * 6. Restore default configuration settings.
 *
 * @ZephyrId MAGETWO-47722, MAGETWO-47723
 */
class ExpireSessionTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Configuration data.
     *
     * @var string
     */
    private $configData;

    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Injection data.
     *
     * @param TestStepFactory $stepFactory
     * @return void
     */
    public function __inject(TestStepFactory $stepFactory)
    {
        $this->stepFactory = $stepFactory;
    }

    /**
     * Runs test.
     *
     * @param int $sessionLifetimeInSeconds
     * @param string $configData
     * @param Customer|null $customer
     * @return void
     */
    public function test(
        $sessionLifetimeInSeconds,
        $configData,
        Customer $customer = null
    ) {
        $this->configData = $configData;
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        if ($customer != null) {
            $customer->persist();
            $this->stepFactory->create(
                \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
                ['customer' => $customer]
            )->run();
        }

        /**
         * Wait admin session to expire.
         */
        sleep($sessionLifetimeInSeconds);
    }

    /**
     * Restore default configuration settings.
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
