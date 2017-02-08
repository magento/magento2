<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestCase;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\User\Test\Fixture\User;
use Magento\User\Test\Page\Adminhtml\UserEdit;

/**
 * Preconditions:
 * 1. Admin user with assigned full access role is created.
 *
 * Steps:
 * 1. Login to Magento admin module with valid credentials.
 * 2. Navigate to System > All Users
 * 3. Click on admin record to open > Account Information page.
 * 4. Update password providing a new password.
 * 5. Save user
 * 6. Repeat Steps 4-5 4 times with different passwords.
 * 7. Update password providing an original password for the user.
 *
 * @ZephyrId MAGETWO-48104
 */
class UpdatePasswordUserEntityPciRequirementsTest extends Injectable
{
    /**
     * User edit page on backend.
     *
     * @var UserEdit
     */
    protected $userEdit;

    /**
     * Dashboard page on backend.
     *
     * @var Dashboard
     */
    protected $dashboard;

    /**
     * Authorization page on backend.
     *
     * @var AdminAuthLogin
     */
    protected $adminAuth;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Setup necessary data for test.
     *
     * @param UserEdit $userEdit
     * @param Dashboard $dashboard
     * @param AdminAuthLogin $adminAuth
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        UserEdit $userEdit,
        Dashboard $dashboard,
        AdminAuthLogin $adminAuth,
        FixtureFactory $fixtureFactory
    ) {
        $this->userEdit = $userEdit;
        $this->dashboard = $dashboard;
        $this->adminAuth = $adminAuth;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Run Test.
     *
     * @param User $user
     * @param array $passwords
     * @return void
     */
    public function test(
        User $user,
        array $passwords
    ) {
        // Preconditions
        $user->persist();
        $initialPassword = $user->getPassword();
        $currentPassword = $user->getPassword();
        $passwords[] = $initialPassword;

        // Steps
        $this->adminAuth->open();
        $this->adminAuth->getLoginBlock()->fill($user);
        $this->adminAuth->getLoginBlock()->submit();

        foreach ($passwords as $password) {
            $data = [
                'password' => $password,
                'password_confirmation' => $password,
                'current_password' => $currentPassword,

            ];
            $updatedUser = $this->fixtureFactory->createByCode('user', ['data' => $data]);

            $this->userEdit->open(['user_id' => $user->getUserId()]);
            $this->userEdit->getUserForm()->fill($updatedUser);
            $this->userEdit->getPageActions()->save();
            $currentPassword = $password;
        }
    }

    /**
     * Logout Admin User from account.
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
