<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\TestCase;

use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\User\Test\Page\Adminhtml\UserIndex;
use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\User\Test\Fixture\User;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create new admin user.
 * 2. Configure 'Maximum Login Failures to Lockout Account'.
 *
 * Steps:
 * 1. Log in to backend as new created admin user.
 * 2. Navigate to System > All Users.
 * 3. Start editing existing User.
 * 4. Fill in all data according to data set (password is incorrect).
 * 5. Perform action 4 specified number of times.
 * 6. Admin account is locked.
 * 7. Perform all assertions.
 *
 * @ZephyrId MAGETWO-49035
 */
class LockAdminUserWhenEditingUserTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S2';
    /* end tags */

    /**
     * User grid page
     *
     * @var UserIndex
     */
    protected $userIndexPage;

    /**
     * User edit page
     *
     * @var UserEdit
     */
    protected $userEditPage;

    /**
     * @var $configData
     */
    protected $configData;

    /**
     * @var AdminAuthLogin page
     */
    protected $adminAuthLogin;

    /**
     * Setup data for test.
     * @param UserIndex $userIndex
     * @param UserEdit $userEdit
     * @param AdminAuthLogin $adminAuthLogin
     */
    public function __inject(
        UserIndex $userIndex,
        UserEdit $userEdit,
        AdminAuthLogin $adminAuthLogin
    ) {
        $this->userIndexPage = $userIndex;
        $this->userEditPage = $userEdit;
        $this->adminAuthLogin = $adminAuthLogin;
    }

    /**
     * Runs Lock admin user when editing existing role test.
     *
     * @param User $user
     * @param int $attempts
     * @param User $customAdmin
     * @param string $configData
     * @return void
     */
    public function test(
        $attempts,
        User $customAdmin,
        User $user,
        $configData
    ) {
        $this->configData = $configData;

        // Preconditions
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customAdmin->persist();

        // Steps login to backend with new user
        $this->adminAuthLogin->open();
        $this->adminAuthLogin->getLoginBlock()->fill($customAdmin);
        $this->adminAuthLogin->getLoginBlock()->submit();
        // Select user to edit.
        $filter = ['username' => $customAdmin->getUsername()];
        $this->userIndexPage->open();
        $this->userIndexPage->getUserGrid()->searchAndOpen($filter);
        // Edit user with wrong password
        for ($i = 0; $i < $attempts; $i++) {
            $this->userEditPage->getUserForm()->fill($user);
            $this->userEditPage->getPageActions()->save();
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
