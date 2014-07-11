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
use Magento\Cms\Test\Page\CmsIndex;
use Magento\User\Test\Fixture\AdminUserInjectable;
use Magento\User\Test\Fixture\AdminUserRole;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;
use Magento\Backend\Test\Page\Dashboard;
use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\User\Test\Page\Adminhtml\UserIndex;

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
     * Run preconditions for test.
     *
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $roleSales = $fixtureFactory->createByCode('adminUserRole', ['dataSet' => 'role_sales']);
        $roleSales->persist();
        return ['roleSales' => $roleSales];
    }

    /**
     * Setup necessary data for test
     *
     * @param UserIndex $userIndex
     * @param UserEdit $userEdit
     * @param Dashboard $dashboard
     * @param AdminAuthLogin $adminAuth
     * @param FixtureFactory $fixtureFactory
     * @return array
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

        $customAdmin = $fixtureFactory->createByCode(
            'adminUserInjectable',
            ['dataSet' => 'custom_admin_with_default_role']
        );
        $customAdmin->persist();

        return [
            'customAdmin' => $customAdmin
        ];
    }

    /**
     * Runs Update Admin User test
     *
     * @param AdminUserInjectable $user
     * @param AdminUserInjectable $customAdmin
     * @param AdminUserRole $roleSales
     * @param string $useSalesRoleFromDataSet
     * @param string $loginAsDefaultAdmin
     * @return void
     */
    public function testUpdateAdminUser(
        AdminUserInjectable $user,
        AdminUserInjectable $customAdmin,
        AdminUserRole $roleSales,
        $useSalesRoleFromDataSet,
        $loginAsDefaultAdmin
    ) {
        // Prepare data
        $filter = ['username' => $customAdmin->getUsername()];
        $userRole = $useSalesRoleFromDataSet != '-' ? $roleSales : null;

        // Steps
        if ($loginAsDefaultAdmin == '0') {
            $this->adminAuth->open();
            $this->adminAuth->getLoginBlock()->fill($customAdmin);
            $this->adminAuth->getLoginBlock()->submit();
        }
        $this->userIndex->open();
        $this->userIndex->getUserGrid()->searchAndOpen($filter);
        $this->userEdit->getUserForm()->fillUser($user, $userRole);
        $this->userEdit->getPageActions()->save();
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
