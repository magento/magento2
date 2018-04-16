<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\User\Test\Fixture\User;

/**
 * Assert that Release Notification Popup is absent on dashboard when admin user login again
 */
class AssertLoginAgainReleaseNotificationPopupNotExist extends AbstractConstraint
{
    /**
     * Assert that Release Notification Popup is absent on dashboard when admin user login again
     *
     * @param Dashboard $dashboard
     * @return void
     */
    public function processAssert(Dashboard $dashboard, User $user)
    {
        $this->objectManager->create(
            \Magento\User\Test\TestStep\LogoutUserOnBackendStep::class
        )->run();

        $this->objectManager->create(
            \Magento\User\Test\TestStep\LoginUserOnBackendStep::class,
            ['user' => $user]
        )->run();

        \PHPUnit_Framework_Assert::assertFalse(
            $dashboard->getReleaseNotificationBlock()->isVisible(),
            "Release Notification Popup is visible on dashboard."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Release Notification Popup is absent on dashboard.";
    }
}
