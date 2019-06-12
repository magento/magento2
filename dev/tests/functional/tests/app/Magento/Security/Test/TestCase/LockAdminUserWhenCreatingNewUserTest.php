<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\TestCase;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Mtf\TestCase\Injectable;
use Magento\User\Test\Fixture\User;
use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\User\Test\Page\Adminhtml\UserIndex;

/**
 * Preconditions:
 * 1. Create admin user.
 * 2. Configure 'Maximum Login Failures to Lockout Account'.
 *
 * Steps:
 * 1. Log in to backend as admin user.
 * 2. Navigate to System > All Users.
 * 3. Click on Add New User.
 * 4. Fill in all data according to data set (password is incorrect).
 * 5. Perform action 4 specified number of times.
 * 6. "The password entered for the current user is invalid. Verify the password and try again." appears after each
 *    attempt.
 * 7. Perform all assertions.
 *
 * @ZephyrId MAGETWO-49034
 */
class LockAdminUserWhenCreatingNewUserTest extends Injectable
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
     * User new/edit page
     *
     * @var UserEdit
     */
    protected $userEditPage;

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
     * Runs Lock admin user when creating new user test.
     *
     * @param int $attempts
     * @param User $customAdmin,
     * @param User $user,
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

        // Steps
        $this->adminAuthLogin->open();
        $this->adminAuthLogin->getLoginBlock()->fill($customAdmin);
        $this->adminAuthLogin->getLoginBlock()->submit();
        $this->userIndexPage->open();
        $this->userIndexPage->getPageActions()->addNew();
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
