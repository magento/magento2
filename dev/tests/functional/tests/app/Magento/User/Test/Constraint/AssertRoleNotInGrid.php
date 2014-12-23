<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Fixture\AdminUserRole;
use Magento\User\Test\Page\Adminhtml\UserRoleIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertRoleNotInGrid
 */
class AssertRoleNotInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Asserts that role is not present in Role Grid.
     *
     * @param UserRoleIndex $rolePage
     * @param AdminUserRole $role
     * @return void
     */
    public function processAssert(
        UserRoleIndex $rolePage,
        AdminUserRole $role
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
