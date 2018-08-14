<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestCase;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\User\Test\Fixture\User;
use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\User\Test\Page\Adminhtml\UserIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for DeleteAdminUserEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Create new admin user and assign it to new role.
 * Steps:
 * 1. Log in as admin user from data set.
 * 2. Go to System>Permissions>All Users
 * 3. Open admin user from precondition
 * 4. Click "Delete User" button
 * 5. Perform all assertions
 *
 * @group ACL
 * @ZephyrId MAGETWO-23416
 */
class DeleteAdminUserEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

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
    protected $adminAuthLogin;

    /**
     * Preparing preconditions for test.
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $user = $fixtureFactory->createByCode(
            'user',
            ['dataset' => 'custom_admin_with_default_role']
        );
        $user->persist();

        return [
            'user' => $user
        ];
    }

    /**
     * Preparing pages for each test iteration.
     *
     * @param UserIndex $userIndex
     * @param UserEdit $userEdit
     * @param Dashboard $dashboard
     * @param AdminAuthLogin $adminAuthLogin
     * @return array
     */
    public function __inject(
        UserIndex $userIndex,
        UserEdit $userEdit,
        Dashboard $dashboard,
        AdminAuthLogin $adminAuthLogin
    ) {
        $this->userIndex = $userIndex;
        $this->userEdit = $userEdit;
        $this->dashboard = $dashboard;
        $this->adminAuthLogin = $adminAuthLogin;
    }

    /**
     * Runs Delete User Entity test
     *
     * @param User $user
     * @param string $isDefaultUser
     * @param User $systemAdmin
     * @return void
     */
    public function testDeleteAdminUserEntity(
        User $user,
        $isDefaultUser,
        User $systemAdmin = null
    ) {
        $filter = [
            'username' => $user->getUsername(),
        ];
        //Steps
        if ($isDefaultUser == 0) {
            $this->adminAuthLogin->open();
            $this->adminAuthLogin->getLoginBlock()->fill($user);
            $this->adminAuthLogin->getLoginBlock()->submit();
        }
        $this->userIndex->open();
        $this->userIndex->getUserGrid()->searchAndOpen($filter);
        $this->userEdit->getUserForm()->fill($systemAdmin);
        $this->userEdit->getPageActions()->delete();
        $this->userEdit->getModalBlock()->acceptAlert();
    }

    /**
     * Logout Admin User from account
     *
     * return void
     */
    public function tearDown()
    {
        $this->dashboard->getAdminPanelHeader()->logOut();
    }
}
