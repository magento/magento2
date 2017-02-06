<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Test\TestCase;

use Magento\User\Test\Page\Adminhtml\UserRoleEditRole;
use Magento\User\Test\Page\Adminhtml\UserRoleIndex;
use Magento\Mtf\TestCase\Injectable;
use Magento\User\Test\Fixture\User;
use Magento\User\Test\Fixture\Role;
use Magento\Backend\Test\Page\AdminAuthLogin;

/**
 * Preconditions:
 * 1. Create new admin user and assign it to new role.
 * 2. Configure 'Maximum Login Failures to Lockout Account'.
 *
 * Steps:
 * 1. Log in to backend as new created admin user.
 * 2. Navigate to System > User Roles.
 * 3. Start editing existing User Role.
 * 4. Fill in all data according to data set (password is incorrect).
 * 5. Perform action 4 specified number of times.
 * 6. Admin account is locked.
 * 7. Perform all assertions.
 *
 * @ZephyrId MAGETWO-49037
 * @Group Security
 *
 */
class LockAdminUserWhenEditingRoleTest extends Injectable
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
     * Admin login Page.
     *
     * @var AdminAuthLogin
     */
    protected $adminAuthLogin;

    /**
     * Setup data for test.
     *
     * @param UserRoleIndex $userRoleIndex
     * @param UserRoleEditRole $userRoleEditRole
     * @param AdminAuthLogin $adminAuthLogin
     * @return void
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
     * Runs Lock admin user when editing existing role test.
     *
     * @param Role $role
     * @param Role $initrole
     * @param int $attempts
     * @param User $customAdmin
     * @param string $configData
     * @return void
     */
    public function test(
        Role $role,
        Role $initrole,
        $attempts,
        User $customAdmin,
        $configData
    ) {
        $this->configData = $configData;
        // Preconditions
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customAdmin->persist();
        $initrole->persist();
        // Steps login to backend with new user
        $this->adminAuthLogin->open();
        $this->adminAuthLogin->getLoginBlock()->fill($customAdmin);
        $this->adminAuthLogin->getLoginBlock()->submit();
        $filter = ['rolename' => $initrole->getRolename()];
        $this->userRoleIndex->open();
        $this->userRoleIndex->getRoleGrid()->searchAndOpen($filter);
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
