<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUserSuccessLogOut
 */
class AssertUserSuccessLogOut extends AbstractConstraint
{
    /**
     * Asserts that 'You have logged out.' message is present on page
     *
     * @param AdminAuthLogin $adminAuth
     * @param Dashboard $dashboard
     * @return void
     */
    public function processAssert(
        AdminAuthLogin $adminAuth,
        Dashboard $dashboard
    ) {
        $dashboard->getAdminPanelHeader()->logOut();
        $isLoginBlockVisible = $adminAuth->getLoginBlock()->isVisible();
        \PHPUnit\Framework\Assert::assertTrue(
            $isLoginBlockVisible,
            'Admin user was not logged out.'
        );
    }

    /**
     * Return message if user successful logout
     *
     * @return string
     */
    public function toString()
    {
        return 'User had successfully logged out.';
    }
}
