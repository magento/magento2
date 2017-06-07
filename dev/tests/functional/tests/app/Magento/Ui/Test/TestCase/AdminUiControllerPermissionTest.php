<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\TestCase;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\User\Test\Fixture\User;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Admin UI controller permission test.
 *
 * Test Flow:
 * Preconditions:
 * 1. Create new admin user and assign it to new role.
 * Steps:
 * 1. Log in as admin user from data set.
 * 2. Go to url from test data in the same browser window.
 * 3. Perform all assertions.
 *
 * @group Ui_(CS)
 * @ZephyrId MAGETWO-64320
 * @security-private
 */
class AdminUiControllerPermissionTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

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
            ['dataset' => 'custom_admin_with_dashboard_role']
        );
        $user->persist();

        return [
            'user' => $user
        ];
    }

    /**
     * Preparing pages for test.
     *
     * @param Dashboard $dashboard
     * @param AdminAuthLogin $adminAuthLogin
     * @return void
     */
    public function __inject(
        Dashboard $dashboard,
        AdminAuthLogin $adminAuthLogin
    ) {
        $this->dashboard = $dashboard;
        $this->adminAuthLogin = $adminAuthLogin;
    }

    /**
     * Runs admin UI controller permission test.
     *
     * @param User $user
     * @return void
     */
    public function test(User $user)
    {
        $this->adminAuthLogin->open();
        $this->adminAuthLogin->getLoginBlock()->fill($user);
        $this->adminAuthLogin->getLoginBlock()->submit();
    }

    /**
     * Logout Admin User from account.
     *
     * return void
     */
    public function tearDown()
    {
        $this->dashboard->getAdminPanelHeader()->logOut();
    }
}
