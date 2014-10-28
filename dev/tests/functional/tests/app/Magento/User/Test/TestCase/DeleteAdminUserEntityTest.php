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

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\User\Test\Page\Adminhtml\UserIndex;
use Mtf\TestCase\Injectable;
use Magento\User\Test\Fixture\User;
use Mtf\Fixture\FixtureFactory;

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
 * @group ACL_(MX)
 * @ZephyrId MAGETWO-23416
 */
class DeleteAdminUserEntityTest extends Injectable
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
            ['dataSet' => 'custom_admin_with_default_role']
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
     * @return void
     */
    public function testDeleteAdminUserEntity(
        User $user,
        $isDefaultUser
    ) {
        $filter = [
            'username' => $user->getUsername()
        ];
        //Steps
        if ($isDefaultUser == 0) {
            $this->adminAuthLogin->open();
            $this->adminAuthLogin->getLoginBlock()->fill($user);
            $this->adminAuthLogin->getLoginBlock()->submit();
        }
        $this->userIndex->open();
        $this->userIndex->getUserGrid()->searchAndOpen($filter);
        $this->userEdit->getPageActions()->delete();
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
