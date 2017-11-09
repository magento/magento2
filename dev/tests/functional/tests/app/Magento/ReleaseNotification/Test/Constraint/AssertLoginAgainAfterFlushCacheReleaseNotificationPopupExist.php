<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\User\Test\Fixture\User;

/**
 * Assert that Release Notification Popup is visible on dashboard when admin user login again after flush cache
 */
class AssertLoginAgainAfterFlushCacheReleaseNotificationPopupExist extends AbstractConstraint
{
    /**
     * Assert that Release Notification Popup is visibile on dashboard when admin user login again after flush cache
     *
     * @param Dashboard $dashboard
     * @param User $user
     * @param AdminCache $adminCache
     * @return void
     */
    public function processAssert(Dashboard $dashboard, User $user, AdminCache $adminCache)
    {
        // Flush cache
        $adminCache->open();
        $adminCache->getActionsBlock()->flushMagentoCache();
        $adminCache->getMessagesBlock()->waitSuccessMessage();

        // Log out
        $dashboard->getAdminPanelHeader()->logOut();

        // Log in again
        $this->objectManager->create(
            \Magento\User\Test\TestStep\LoginUserOnBackendStep::class,
            ['user' => $user]
        )->run();

        \PHPUnit_Framework_Assert::assertTrue(
            $dashboard->getReleaseNotificationBlock()->isVisible(),
            "Release Notification Popup is absent on dashboard."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Release Notification Popup is visible on dashboard.";
    }
}
