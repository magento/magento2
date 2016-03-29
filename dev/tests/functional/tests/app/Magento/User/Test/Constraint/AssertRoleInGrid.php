<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Fixture\Role;
use Magento\User\Test\Page\Adminhtml\UserRoleIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertRoleInGrid
 */
class AssertRoleInGrid extends AbstractConstraint
{
    /**
     * Asserts that saved role is present in Role Grid.
     *
     * @param UserRoleIndex $rolePage
     * @param Role $role
     * @param Role $roleInit
     * @return void
     */
    public function processAssert(
        UserRoleIndex $rolePage,
        Role $role,
        Role $roleInit = null
    ) {
        $filter = ['rolename' => $role->hasData('rolename') ? $role->getRoleName() : $roleInit->getRoleName()];
        $rolePage->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $rolePage->getRoleGrid()->isRowVisible($filter),
            'Role with name \'' . $filter['rolename'] . '\' is absent in Roles grid.'
        );
    }

    /**
     * Returns success message if assert true.
     *
     * @return string
     */
    public function toString()
    {
        return 'Role is present in Roles grid.';
    }
}
