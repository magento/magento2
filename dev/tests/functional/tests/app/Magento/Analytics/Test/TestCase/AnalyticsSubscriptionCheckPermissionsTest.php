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
 */
class AnalyticsSubscriptionCheckPermissionsTest extends Scenario
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Preconditions for test.
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $this->fixtureFactory = $fixtureFactory;
        $user = $fixtureFactory
            ->createByCode('user', ['dataset' => 'custom_admin_with_role_without_subscription_permissions']);
        $user->persist();

        return ['user' => $user];
    }

    /**
     * Inject additional arguments.
     *
     * @param User $user
     *
     * @return array
     */
    public function __inject(User $user)
    {
        return ['user' => $user];
    }

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
