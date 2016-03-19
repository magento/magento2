<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Fixture\User;
use Magento\User\Test\Page\Adminhtml\UserIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUserNotInGrid
 */
class AssertUserNotInGrid extends AbstractConstraint
{
    /**
     * Asserts that User is not present in User Grid.
     *
     * @param UserIndex $userIndex
     * @param User $user
     * @return void
     */
    public function processAssert(
        UserIndex $userIndex,
        User $user
    ) {
        $filter = ['username' => $user->getUsername()];
        $userIndex->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $userIndex->getUserGrid()->isRowVisible($filter),
            'User with name \'' . $user->getUsername() . '\' is present in Users grid.'
        );
    }

    /**
     * Returns message if user not in grid.
     *
     * @return string
     */
    public function toString()
    {
        return 'User is absent in Users grid.';
    }
}
