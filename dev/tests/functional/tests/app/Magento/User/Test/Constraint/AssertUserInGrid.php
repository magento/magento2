<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Fixture\User;
use Magento\User\Test\Page\Adminhtml\UserIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Asserts that user is present in User Grid.
 */
class AssertUserInGrid extends AbstractConstraint
{
    /**
     * Asserts that user is present in User Grid.
     *
     * @param UserIndex $userIndex
     * @param User $user
     * @return void
     */
    public function processAssert(UserIndex $userIndex, User $user)
    {
        $filter = ['username' => $user->getUsername()];

        $userIndex->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $userIndex->getUserGrid()->isRowVisible($filter),
            'User with name \'' . $user->getUsername() . '\' is absent in User grid.'
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
