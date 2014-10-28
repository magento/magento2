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
use Magento\User\Test\Page\Adminhtml\UserRoleIndex;
use Magento\User\Test\Page\Adminhtml\UserRoleEditRole;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for CreateAdminUserRolesEntity
 *
 * Test Flow:
 * 1. Log in as default admin user
 * 2. Go to System>Permissions>User Roles
 * 3. Press "+" button to start create New Role
 * 4. Fill in all data according to data set
 * 5. Save role
 * 6. Perform assertions
 *
 * @group ACL_(MX)
 * @ZephyrId MAGETWO-23413
 */
class CreateAdminUserRoleEntityTest extends Injectable
{
    /**
     * @var UserRoleIndex
     */
    protected $userRoleIndex;

    /**
     * @var UserRoleEditRole
     */
    protected $userRoleEditRole;

    /**
     * Setup data for test
     *
     * @param UserRoleIndex $userRoleIndex
     * @param UserRoleEditRole $userRoleEditRole
     */
    public function __inject(
        UserRoleIndex $userRoleIndex,
        UserRoleEditRole $userRoleEditRole
    ) {
        $this->userRoleIndex = $userRoleIndex;
        $this->userRoleEditRole = $userRoleEditRole;
    }

    /**
     * Runs Create Admin User Role Entity test.
     *
     * @param AdminUserRole $role
     */
    public function testCreateUserRole(AdminUserRole $role)
    {
        //Steps
        $this->userRoleIndex->open();
        $this->userRoleIndex->getRoleActions()->addNew();
        $this->userRoleEditRole->getRoleFormTabs()->fill($role);
        $this->userRoleEditRole->getPageActions()->save();
    }
}
