<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\User\Test\TestCase;

use Magento\User\Test\Fixture\AdminUserRole;
use Magento\User\Test\Fixture\User;
use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\User\Test\Page\Adminhtml\UserIndex;
use Magento\User\Test\Page\Adminhtml\UserRoleEditRole;
use Magento\User\Test\Page\Adminhtml\UserRoleIndex;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

/**
 * Test that user can login from the first attempt just after his permissions were changed.
 *
 * Test Flow:
 * 1. Log in as default admin user
 * 2. Go to System>Permissions>User Roles
 * 3. Press "+" button to start create New Role
 * 4. Fill in all data according to data set
 * 5. Save role
 * 6. Go to System-Permissions-All Users
 * 7. Press "+" button to start create new admin user
 * 8. Fill in all data according to data set
 * 9. Save user
 * 10. Go to System>Permissions>User Roles
 * 11. Open created role, and change permissions to 'all'
 * 12. Log out
 * 13. Log in using new admin user (before the bug was fixed, it was impossible to log in from the first attempt)
 * 14. Perform assertions
 *
 * @group ACL_(MX)
 * @ZephyrId MAGETWO-28828
 */
class UserLoginAfterChangingPermissionsTest extends Injectable
{
    /**
     * User edit page
     *
     * @var UserRoleIndex
     */
    protected $userRoleIndex;

    /**
     * Role edit page
     *
     * @var UserRoleEditRole
     */
    protected $userRoleEditRole;

    /**
     * User grid page
     *
     * @var UserIndex
     */
    protected $userIndexPage;

    /**
     * User new/edit page
     *
     * @var UserEdit
     */
    protected $userEditPage;

    /**
     * Dashboard panel
     *
     * @var Dashboard
     */
    protected $dashboard;

    /**
     * Factory for Fixtures
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Preconditions for test
     *
     * @param FixtureFactory $fixtureFactory
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Setup necessary data for test
     *
     * @param UserIndex $userIndex
     * @param UserEdit $userEdit
     * @param UserRoleIndex $userRoleIndex
     * @param UserRoleEditRole $userRoleEditRole
     * @param Dashboard $dashboard
     * @return void
     */
    public function __inject(
        UserIndex $userIndex,
        UserEdit $userEdit,
        UserRoleIndex $userRoleIndex,
        UserRoleEditRole $userRoleEditRole,
        Dashboard $dashboard
    ) {
        $this->userIndexPage = $userIndex;
        $this->userEditPage = $userEdit;
        $this->userRoleIndex = $userRoleIndex;
        $this->userRoleEditRole = $userRoleEditRole;
        $this->dashboard = $dashboard;
    }

    /**
     * @param AdminUserRole $role
     * @param AdminUserRole $updatedRole
     * @param User $user
     * @return void
     */
    public function testLoginAfterChangingPermissions(
        AdminUserRole $role,
        AdminUserRole $updatedRole,
        User $user
    ) {
        /** Create role and a new user with this role */
        $role->persist();
        /** @var User $user */
        $user = $this->fixtureFactory->create(
            'Magento\User\Test\Fixture\User',
            ['data' => array_merge($user->getData(), ['role_id' => ['role' => $role]])]
        );
        $user->persist();

        /** Change the scope of resources available for the role created earlier */
        $filter = ['rolename' => $role->getRoleName()];
        $this->userRoleIndex->open();
        $this->userRoleIndex->getRoleGrid()->searchAndOpen($filter);
        $this->userRoleEditRole->getRoleFormTabs()->fill($updatedRole);
        $this->userRoleEditRole->getPageActions()->save();
        $this->dashboard->getAdminPanelHeader()->logOut();
    }
}
