<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\User\Test\Fixture\User;

/**
 * Assert that Advanced Reporting Popup is absent on dashboard when admin user login again
 */
class AssertLoginAgainAdvancedReportingPopupNotExist extends AbstractConstraint
{
    /**
     * Assert that Advanced Reporting Popup is absent on dashboard when admin user login again
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
            $dashboard->getAdvancedReportingBlock()->isVisible(),
            "Advanced Reporting Popup is visible on dashboard."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Advanced Reporting Popup is absent on dashboard.";
    }
}
