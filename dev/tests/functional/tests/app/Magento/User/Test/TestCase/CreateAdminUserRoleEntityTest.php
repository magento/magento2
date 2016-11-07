<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\TestCase;

use Magento\User\Test\Fixture\Role;
use Magento\User\Test\Page\Adminhtml\UserRoleEditRole;
use Magento\User\Test\Page\Adminhtml\UserRoleIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Flow:
 * 1. Log in as default admin user
 * 2. Go to System>Permissions>User Roles
 * 3. Press "+" button to start create New Role
 * 4. Fill in all data according to data set
 * 5. Save role
 * 6. Perform assertions
 *
 * @group ACL
 * @ZephyrId MAGETWO-23413
 */
class CreateAdminUserRoleEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

    /**
     * UserRoleIndex page.
     *
     * @var UserRoleIndex
     */
    protected $userRoleIndex;

    /**
     * UserRoleEditRole page.
     *
     * @var UserRoleEditRole
     */
    protected $userRoleEditRole;

    /**
     * Setup data for test.
     *
     * @param UserRoleIndex $userRoleIndex
     * @param UserRoleEditRole $userRoleEditRole
     */
    public function __inject(UserRoleIndex $userRoleIndex, UserRoleEditRole $userRoleEditRole)
    {
        $this->userRoleIndex = $userRoleIndex;
        $this->userRoleEditRole = $userRoleEditRole;
    }

    /**
     * Runs Create Admin User Role Entity test.
     *
     * @param Role $role
     */
    public function testCreateUserRole(Role $role)
    {
        //Steps
        $this->userRoleIndex->open();
        $this->userRoleIndex->getRoleActions()->addNew();
        $this->userRoleEditRole->getRoleFormTabs()->fill($role);
        $this->userRoleEditRole->getPageActions()->save();
    }
}
