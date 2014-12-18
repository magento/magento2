<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Fixture\AdminUserRole;
use Magento\User\Test\Page\Adminhtml\UserRoleIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertRoleInGrid
 */
class AssertRoleInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Asserts that saved role is present in Role Grid.
     *
     * @param UserRoleIndex $rolePage
     * @param AdminUserRole $role
     * @param AdminUserRole $roleInit
     * @return void
     */
    public function processAssert(
        UserRoleIndex $rolePage,
        AdminUserRole $role,
        AdminUserRole $roleInit = null
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
