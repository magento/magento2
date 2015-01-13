<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestCase;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\User\Test\Fixture\User;
use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\User\Test\Page\Adminhtml\UserIndex;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for UpdateAdminUserEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Admin user with assigned full access role is created.
 * 2. Custom role with restricted permission: Sales is created
 *
 * Steps:
 * 1. Log in as admin user from data set
 * 2. Navigate to  System>Permissions>All Users
 * 3. Open user from precondition.
 * 4. Fill in all data according to data set
 * 5. Save user
 * 6. Perform all assertions
 *
 * @group ACL_(MX)
 * @ZephyrId MAGETWO-24345
 */
class UpdateAdminUserEntityTest extends Injectable
{
    /**
     * @var UserIndex
     */
    protected $userIndex;

    /**
     * @var UserEdit
     */
    protected $userEdit;

    /**
     * @var Dashboard
     */
    protected $dashboard;

    /**
     * @var AdminAuthLogin
     */
    protected $adminAuth;

    /**
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Setup necessary data for test
     *
     * @param UserIndex $userIndex
     * @param UserEdit $userEdit
     * @param Dashboard $dashboard
     * @param AdminAuthLogin $adminAuth
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        UserIndex $userIndex,
        UserEdit $userEdit,
        Dashboard $dashboard,
        AdminAuthLogin $adminAuth,
        FixtureFactory $fixtureFactory
    ) {
        $this->userIndex = $userIndex;
        $this->userEdit = $userEdit;
        $this->dashboard = $dashboard;
        $this->adminAuth = $adminAuth;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Runs Update Admin User test
     *
     * @param User $user
     * @param User $initialUser
     * @param string $loginAsDefaultAdmin
     * @return array
     */
    public function testUpdateAdminUser(
        User $user,
        User $initialUser,
        $loginAsDefaultAdmin
    ) {
        // Precondition
        $initialUser->persist();

        // Steps
        $filter = ['username' => $initialUser->getUsername()];
        if ($loginAsDefaultAdmin == '0') {
            $this->adminAuth->open();
            $this->adminAuth->getLoginBlock()->fill($initialUser);
            $this->adminAuth->getLoginBlock()->submit();
        }
        $this->userIndex->open();
        $this->userIndex->getUserGrid()->searchAndOpen($filter);
        $this->userEdit->getUserForm()->fill($user);
        $this->userEdit->getPageActions()->save();

        return ['customAdmin' => $this->mergeUsers($user, $initialUser)];
    }

    /**
     * Merging user data and returns custom user
     *
     * @param User $user
     * @param User $initialUser
     * @return User
     */
    protected function mergeUsers(
        User $user,
        User $initialUser
    ) {
        $data = array_merge($initialUser->getData(), $user->getData());
        if (isset($data['role_id'])) {
            $data['role_id'] = [
                'role' => ($user->hasData('role_id'))
                    ? $user->getDataFieldConfig('role_id')['source']->getRole()
                    : $initialUser->getDataFieldConfig('role_id')['source']->getRole(),
            ];
        }

        return $this->fixtureFactory->createByCode('user', ['data' => $data]);
    }

    /**
     * Logout Admin User from account
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->dashboard->getAdminPanelHeader()->isVisible()) {
            $this->dashboard->getAdminPanelHeader()->logOut();
        }
    }
}
