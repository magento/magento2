<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\User\Test\Fixture\User;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUserSuccessLogin
 */
class AssertUserSuccessLogin extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Verify whether customer has logged in to the Backend
     *
     * @param User $user
     * @param AdminAuthLogin $adminAuth
     * @param Dashboard $dashboard
     * @param User $customAdmin
     * @internal param null|string $userToLoginInAssert
     * @return void
     */
    public function processAssert(
        User $user,
        AdminAuthLogin $adminAuth,
        Dashboard $dashboard,
        User $customAdmin = null
    ) {
        $adminAuth->open();
        $adminUser = $customAdmin === null ? $user : $customAdmin;
        if ($dashboard->getAdminPanelHeader()->isVisible()) {
            $dashboard->getAdminPanelHeader()->logOut();
        }
        $adminAuth->getLoginBlock()->fill($adminUser);
        $adminAuth->getLoginBlock()->submit();

        \PHPUnit_Framework_Assert::assertTrue(
            $dashboard->getAdminPanelHeader()->isLoggedIn(),
            'Admin user was not logged in.'
        );
    }

    /**
     * Returns success message if equals to expected message
     *
     * @return string
     */
    public function toString()
    {
        return 'Admin user is logged in.';
    }
}
