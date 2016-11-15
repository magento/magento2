<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\User\Test\Fixture\User;

/**
 * Preconditions:
 * 1. Enable 'Add Store Code to Urls'.
 * 2. Log out from Admin.
 *
 * Steps:
 * 1. Log in to Admin.
 * 2. Perform all assertions.
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
     * Log out from Admin and log in again.
     *
     * @param string $configData
     * @param User $user
     * @return void
     */
    public function test($configData, User $user)
    {
        //Preconditions
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $user->persist();
    }

    /**
     * Reset config settings to default.
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
