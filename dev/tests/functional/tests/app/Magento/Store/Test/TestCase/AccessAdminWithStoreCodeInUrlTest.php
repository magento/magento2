<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Steps:
 * 1. Enable 'Add Store Code to Urls'.
 * 2. Log out from Admin.
 * 3. Perform all assertions.
 *
 * @ZephyrId MAGETWO-42720
 */
class AccessAdminWithStoreCodeInUrlTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * Step factory.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Inject step factory.
     *
     * @param TestStepFactory $stepFactory
     * @return void
     */
    public function __inject(TestStepFactory $stepFactory)
    {
        $this->stepFactory = $stepFactory;
    }

    /**
     * Set config and log out from Admin.
     *
     * @param string $configData
     * @return void
     */
    public function test($configData)
    {
        $this->configData = $configData;
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $this->stepFactory->create(
            \Magento\User\Test\TestStep\LogoutUserOnBackendStep::class,
            ['configData' => $this->configData]
        )->run();
    }

    /**
     * Reset config settings to default.
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
