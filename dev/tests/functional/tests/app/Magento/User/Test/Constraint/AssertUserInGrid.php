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

namespace Magento\User\Test\Constraint;

use Magento\User\Test\Fixture\User;
use Mtf\Constraint\AbstractConstraint;
use Magento\User\Test\Page\Adminhtml\UserIndex;

/**
 * Class AssertUserInGrid
 */
class AssertUserInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
