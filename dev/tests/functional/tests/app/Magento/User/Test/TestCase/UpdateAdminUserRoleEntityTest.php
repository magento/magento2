<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestCase;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\User\Test\Fixture\Role;
use Magento\User\Test\Fixture\User;
use Magento\User\Test\Page\Adminhtml\UserRoleEditRole;
use Magento\User\Test\Page\Adminhtml\UserRoleIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for UpdateAdminUserRoleEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Create new admin user and assign it to new role.
 * Steps:
 * 1. Log in as admin user from data set.
 * 2. Go to System>Permissions>User Roles
 * 3. Open role created in precondition
 * 4. Fill in data according to data set
 * 5. Perform all assertions
 *
 * @group ACL
 * @ZephyrId MAGETWO-24768
 */
class UpdateAdminUserRoleEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * @var UserRoleIndex
     */
    protected $rolePage;

    /**
     * @var UserRoleEditRole
     */
    protected $userRoleEditRole;

    /**
     * @var AdminAuthLogin
     */
    protected $adminAuthLogin;

    /**
     * @var Dashboard
     */
    protected $dashboard;

    /**
     * Setup data for test
     *
     * @param UserRoleIndex $rolePage
     * @param UserRoleEditRole $userRoleEditRole
     * @param AdminAuthLogin $adminAuthLogin
     * @param Dashboard $dashboard
     * @return void
     */
    public function __inject(
        UserRoleIndex $rolePage,
        UserRoleEditRole $userRoleEditRole,
        AdminAuthLogin $adminAuthLogin,
        Dashboard $dashboard
    ) {
        $this->rolePage = $rolePage;
        $this->userRoleEditRole = $userRoleEditRole;
        $this->adminAuthLogin = $adminAuthLogin;
        $this->dashboard = $dashboard;
    }

    /**
     * Runs Update Admin User Roles Entity test
     *
     * @param Role $roleInit
     * @param Role $role
     * @param User $user
     * @return array
     */
    public function testUpdateAdminUserRolesEntity(
        Role $roleInit,
        Role $role,
        User $user
    ) {

        // Preconditions
        $roleInit->persist();
        if (!$user->hasData('user_id')) {
            $user->persist();
        }

        // Steps
        $filter = ['rolename' => $roleInit->getRoleName()];
        $this->adminAuthLogin->open();
        $this->adminAuthLogin->getLoginBlock()->fill($user);
        $this->adminAuthLogin->getLoginBlock()->submit();
        $this->rolePage->open();
        $this->rolePage->getRoleGrid()->searchAndOpen($filter);
        $this->userRoleEditRole->getRoleFormTabs()->fill($role);
        $this->userRoleEditRole->getPageActions()->save();

        return [
            'user' => $role->hasData('in_role_users')
                ? $role->getDataFieldConfig('in_role_users')['source']->getAdminUsers()[0]
                : $user,
        ];
    }

    /**
     * Logout Admin User from account
     *
     * @return void
     */
    public function tearDown()
    {
        sleep(3);
        $modalMessage = $this->dashboard->getModalMessage();
        if ($modalMessage->isVisible()) {
            $modalMessage->acceptAlert();
        }
        $this->dashboard->getAdminPanelHeader()->logOut();
    }
}
