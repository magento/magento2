<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestCase;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Config\Test\Fixture\ConfigData;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\User\Test\Fixture\User;
use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\User\Test\Page\Adminhtml\UserIndex;

/**
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
 * @group ACL
 * @ZephyrId MAGETWO-24345
 */
class UpdateAdminUserEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    const SEVERITY = 'S3';
    /* end tags */

    /**
     * User list page on backend.
     *
     * @var UserIndex
     */
    protected $userIndex;

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
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    protected $testStepFactory;

    /**
     * @var string
     */
    private $configData;

    /**
     * Setup necessary data for test.
     *
     * @param UserIndex $userIndex
     * @param UserEdit $userEdit
     * @param Dashboard $dashboard
     * @param AdminAuthLogin $adminAuth
     * @param FixtureFactory $fixtureFactory
     * @param TestStepFactory $testStepFactory
     * @return void
     */
    public function __inject(
        UserIndex $userIndex,
        UserEdit $userEdit,
        Dashboard $dashboard,
        AdminAuthLogin $adminAuth,
        FixtureFactory $fixtureFactory,
        TestStepFactory $testStepFactory
    ) {
        $this->userIndex = $userIndex;
        $this->userEdit = $userEdit;
        $this->dashboard = $dashboard;
        $this->adminAuth = $adminAuth;
        $this->fixtureFactory = $fixtureFactory;
        $this->testStepFactory = $testStepFactory;
    }

    /**
     * Runs Update Admin User test.
     *
     * @param User $initialUser
     * @param User $user
     * @param string $loginAsDefaultAdmin
     * @param ConfigData $config
     * @param string $configData
     * @return array
     * @throws \Exception
     */
    public function testUpdateAdminUser(
        User $initialUser,
        User $user,
        $loginAsDefaultAdmin,
        ConfigData $config,
        $configData = null
    ) {
        // Preconditions
        $this->configData = $configData;
        $config->persist();
        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();
        $initialUser->persist();

        // Steps
        $filter = ['username' => $initialUser->getUsername()];
        if ($loginAsDefaultAdmin == '0') {
            $this->adminAuth->open();
            $this->adminAuth->getLoginBlock()->fill($initialUser);
            $this->adminAuth->getLoginBlock()->submit();
            $this->adminAuth->waitForHeaderBlock();
            $this->adminAuth->dismissAdminUsageNotification();
        }
        $this->userIndex->open();
        $this->userIndex->getUserGrid()->searchAndOpen($filter);
        $this->userEdit->getUserForm()->fill($user);
        $this->userEdit->getPageActions()->save();

        return ['user' => $this->mergeUsers($initialUser, $user)];
    }

    /**
     * Merging user data and returns custom user.
     *
     * @param User $initialUser
     * @param User $user
     * @return User
     */
    protected function mergeUsers(User $initialUser, User $user)
    {
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
