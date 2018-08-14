<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Fixture\Role;
use Magento\User\Test\Page\Adminhtml\UserRoleIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertRoleNotInGrid
 */
class AssertRoleNotInGrid extends AbstractConstraint
{
    /**
     * Asserts that role is not present in Role Grid.
     *
     * @param UserRoleIndex $rolePage
     * @param Role $role
     * @return void
     */
    public function processAssert(
        UserRoleIndex $rolePage,
        Role $role
    ) {
        $filter = ['rolename' => $role->getRoleName()];
        $rolePage->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $rolePage->getRoleGrid()->isRowVisible($filter),
            'Role with name \'' . $role->getRoleName() . '\' is present in Roles grid.'
        );
    }

    /**
     * Returns success message if assert true.
     *
     * @return string
     */
    public function toString()
    {
        return 'Role is absent in Roles grid.';
    }
}
