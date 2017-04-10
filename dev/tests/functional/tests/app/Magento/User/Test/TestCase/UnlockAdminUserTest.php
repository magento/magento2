<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestCase;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\User\Test\Fixture\User;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\User\Test\Page\Adminhtml\UserLocks;

/**
 * Preconditions:
 * 1. Create custom admin user.
 * 2. Configure 'Maximum Login Failures to Lockout Account'.
 * 3. Lock custom admin user.
 *
 * Steps:
 * 1. Login with the default admin.
 * 2. Go to Locked Users page.
 * 3. Unlock custom admin user.
 * 4. Perform all assertions.
 *
 * @ZephyrId MAGETWO-12484
 */
class UnlockAdminUserTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    protected $testStepFactory;

    /**
     * Admin login page.
     *
     * @var AdminAuthLogin
     */
    protected $adminAuth;

    /**
     * Locked users page.
     *
     * @var UserLocks
     */
    protected $userLocks;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Config data.
     *
     * @var string
     */
    protected $configData;

    /**
     * Injection data.
     *
     * @param TestStepFactory $testStepFactory
     * @param AdminAuthLogin $adminAuth
     * @param FixtureFactory $fixtureFactory
     * @param UserLocks $userLocks
     * @return void
     */
    public function __inject(
        TestStepFactory $testStepFactory,
        AdminAuthLogin $adminAuth,
        FixtureFactory $fixtureFactory,
        UserLocks $userLocks
    ) {
        $this->testStepFactory = $testStepFactory;
        $this->adminAuth = $adminAuth;
        $this->fixtureFactory = $fixtureFactory;
        $this->userLocks = $userLocks;
    }

    /**
     * Check that login works correctly after unlocking admin user.
     *
     * @param User $customAdmin
     * @param string $incorrectPassword
     * @param int $attempts
     * @param string $configData
     * @return array
     */
    public function test(
        User $customAdmin,
        $incorrectPassword,
        $attempts,
        $configData
    ) {
        // Preconditions
        $this->configData = $configData;
        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();
        $customAdmin->persist();
        /** @var User $incorrectUser */
        $incorrectUser = $this->fixtureFactory->createByCode(
            'user',
            ['data' => ['username' => $customAdmin->getUsername(), 'password' => $incorrectPassword]]
        );
        for ($i = 0; $i < $attempts; $i++) {
            $this->adminAuth->open();
            $this->adminAuth->getLoginBlock()->fill($incorrectUser);
            $this->adminAuth->getLoginBlock()->submit();
        }

        // Test steps
        $this->userLocks->open();
        $this->userLocks->getLockedUsersGrid()->massaction([['username' => $customAdmin->getUsername()]], 'Unlock');

        return ['user' => $customAdmin];
    }

    /**
     * Revert configuration settings.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
