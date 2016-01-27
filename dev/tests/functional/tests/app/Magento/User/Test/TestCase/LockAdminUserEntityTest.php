<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestCase;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Config\Test\Fixture\ConfigData;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\User\Test\Constraint\AssertUserFailedLoginMessage;
use Magento\User\Test\Fixture\User;

/**
 * Preconditions:
 * 1. Create admin user.
 * 2. Configure 'Maximum Login Failures to Lockout Account'.
 *
 * Steps:
 * 1. Open Magento admin user login page.
 * 2. Enter incorrect password specified number of times.
 * 3. "You did not sign in correctly or your account is temporarily disabled." appears after each login attempt.
 * 4. Perform all assertions.
 *
 * @group AuthN_&_AuthZ_(PS)
 * @ZephyrId MAGETWO-12386
 */
class LockAdminUserEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'PS';
    /* end tags */

    /**
     * Check that locked user can't log in to admin panel.
     *
     * @param ConfigData $config
     * @param User $customAdmin
     * @param string $incorrectPassword
     * @param int $attempts
     * @param AdminAuthLogin $adminAuth
     * @param FixtureFactory $fixtureFactory
     * @param AssertUserFailedLoginMessage $assertUserFailedLoginMessage
     * @return array
     */
    public function testUpdateAdminUser(
        ConfigData $config,
        User $customAdmin,
        $incorrectPassword,
        $attempts,
        AdminAuthLogin $adminAuth,
        FixtureFactory $fixtureFactory,
        AssertUserFailedLoginMessage $assertUserFailedLoginMessage
    ) {
        // Preconditions
        $config->persist();
        $customAdmin->persist();
        /** @var User $incorrectUser */
        $incorrectUser = $fixtureFactory->createByCode(
            'user',
            ['data' => ['username' => $customAdmin->getUsername(), 'password' => $incorrectPassword]]
        );

        // Steps and assertions
        for ($i = 0; $i < $attempts; $i++) {
            $assertUserFailedLoginMessage->processAssert($adminAuth, $incorrectUser);
        }
    }
}
