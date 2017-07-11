<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\Backend\Test\Page\AdminAuthLogin;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Config\Test\Page\Adminhtml\ConfigIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\User\Test\Fixture\User;

/**
 * Assert for check permission for admin user that has access only to Configuration index page
 * (without access to any sections).
 */
class AssertUserPermissionsOnlyConfigurationIndexPage extends AbstractConstraint
{
    /**
     * Check if form is empty.
     *
     * @param ConfigIndex $configIndex
     * @param Dashboard $dashboard
     * @param AdminAuthLogin $adminAuth
     * @param User $customAdmin
     */
    public function processAssert(
        ConfigIndex $configIndex,
        Dashboard $dashboard,
        AdminAuthLogin $adminAuth,
        User $customAdmin
    ) {
        $dashboard->getAdminPanelHeader()->logOut();
        $adminAuth->open();
        $adminAuth->getLoginBlock()->fill($customAdmin);
        $adminAuth->getLoginBlock()->submit();
        $configIndex->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $configIndex->getAdminForm()->isEmpty(),
            "Form isn't empty."
        );
    }

    /**
     * Return string representation of object
     *
     * @return string
     */
    public function toString()
    {
        return 'Form is empty.';
    }
}
