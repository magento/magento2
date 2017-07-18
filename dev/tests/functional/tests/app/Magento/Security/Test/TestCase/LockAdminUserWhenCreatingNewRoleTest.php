<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\TestCase;

use Magento\User\Test\Fixture\Role;
use Magento\User\Test\Page\Adminhtml\UserRoleEditRole;
use Magento\User\Test\Page\Adminhtml\UserRoleIndex;
use Magento\Mtf\TestCase\Injectable;
use Magento\User\Test\Fixture\User;
use Magento\Backend\Test\Page\AdminAuthLogin;

/**
 * Preconditions:
 * 1. Create admin user.
 * 2. Configure 'Maximum Login Failures to Lockout Account'.
 *
 * Steps:
 * 1. Log in to backend as admin user.
 * 2. Navigate to System > Extensions > User Roles.
 * 3. Start to create new User Role.
 * 4. Fill in all data according to data set (password is incorrect).
 * 5. Perform action 4 specified number of times.
 * 6. "You have entered an invalid password for current user." appears after each attempt.
 * 7. Perform all assertions.
 *
 * @ZephyrId MAGETWO-49036
 */
class LockAdminUserWhenCreatingNewRoleTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S2';
    /* end tags */

    /**
     * UserRoleIndex page.
     *
     * @var UserRoleIndex
     */
    protected $userRoleIndex;

    /**
     * UserRoleEditRole page.
     *
     * @var UserRoleEditRole
     */
    protected $userRoleEditRole;

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * @var AdminAuthLogin page
     */
    protected $adminAuthLogin;

    /**
     * Setup data for test.
     *
     * @param UserRoleIndex $userRoleIndex
     * @param UserRoleEditRole $userRoleEditRole
     * @param AdminAuthLogin $adminAuthLogin
     */
    public function __inject(
        UserRoleIndex $userRoleIndex,
        UserRoleEditRole $userRoleEditRole,
        AdminAuthLogin $adminAuthLogin
    ) {
        $this->userRoleIndex = $userRoleIndex;
        $this->userRoleEditRole = $userRoleEditRole;
        $this->adminAuthLogin = $adminAuthLogin;
    }

    /**
     * Runs Lock admin user when creating new role test.
     *
     * @param Role $role
     * @param int $attempts
     * @param User $customAdmin,
     * @param string $configData
     * @return void
     */
    public function testLockAdminUser(
        Role $role,
        $attempts,
        User $customAdmin,
        $configData = null
    ) {
        $this->configData = $configData;

        // Preconditions
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customAdmin->persist();

        // Steps
        $this->adminAuthLogin->open();
        $this->adminAuthLogin->getLoginBlock()->fill($customAdmin);
        $this->adminAuthLogin->getLoginBlock()->submit();

        $this->userRoleIndex->open();
        $this->userRoleIndex->getRoleActions()->addNew();
        for ($i = 0; $i < $attempts; $i++) {
            $this->userRoleEditRole->getRoleFormTabs()->fill($role);
            $this->userRoleEditRole->getPageActions()->save();
        }

        // Reload
        $this->adminAuthLogin->open();
        $this->adminAuthLogin->getLoginBlock()->fill($customAdmin);
        $this->adminAuthLogin->getLoginBlock()->submit();
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
