<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUserSuccessLogOut
 */
class AssertUserSuccessLogOut extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

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
        \PHPUnit_Framework_Assert::assertTrue(
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
