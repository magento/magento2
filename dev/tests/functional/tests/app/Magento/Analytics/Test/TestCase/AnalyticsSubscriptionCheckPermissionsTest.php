<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Scenario;
use Magento\User\Test\Fixture\User;

/**
 * Preconditions:
 * 1. Create admin user without permissions subscribe to Magento BI.
 *
 * Steps:
 * 1. Login to the admin panel with the newly created admin user.
 * 2. Navigate to dashboard.
 * 3. Assert that subscription pop-up is not visible.
 *
 * @ZephyrId MAGETWO-63206
 */
class AnalyticsSubscriptionCheckPermissionsTest extends Scenario
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
