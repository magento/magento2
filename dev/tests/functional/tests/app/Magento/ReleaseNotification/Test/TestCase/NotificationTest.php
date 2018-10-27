<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;

/**
 * Preconditions:
 * 1. Create admin user without permissions subscribe to Magento BI.
 *
 * Steps:
 * 1. Login to the admin panel with the newly created admin user.
 * 2. Navigate to dashboard.
 * 3. Assert that release notification pop-up is visible.
 *
 * @ZephyrId MAGETWO-80786
 */
class NotificationTest extends Scenario
{
    /* tags */
    const MVP = 'no';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Test execution.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }
}
