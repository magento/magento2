<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Fixture\User;
use Magento\User\Test\Page\Adminhtml\UserIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUserInGrid
 */
class AssertUserInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Asserts that user is present in User Grid.
     *
     * @param UserIndex $userIndex
     * @param User $user
     * @param User $customAdmin
     * @return void
     */
    public function processAssert(
        UserIndex $userIndex,
        User $user,
        User $customAdmin = null
    ) {
        $adminUser = ($user->hasData('password') || $user->hasData('username')) ? $user : $customAdmin;
        $filter = ['username' => $adminUser->getUsername()];
        $userIndex->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $userIndex->getUserGrid()->isRowVisible($filter),
            'User with name \'' . $adminUser->getUsername() . '\' is absent in User grid.'
        );
    }

    /**
     * Returns success message if assert true.
     *
     * @return string
     */
    public function toString()
    {
        return 'User is present in Users grid.';
    }
}
