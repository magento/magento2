<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that subscription form is absent on dashboard for user which doesn't have permissions on it.
 */
class AssertSubscriptionPopupNotExist extends AbstractConstraint
{
    /**
     * Assert that subscription form is absent on dashboard} for user which doesn't have permissions on it.
     *
     * @param Dashboard $dashboard
     * @return void
     */
    public function processAssert(Dashboard $dashboard)
    {
        \PHPUnit_Framework_Assert::assertFalse(
            $dashboard->getSubscriptionBlock()->isVisible(),
            "Subscription form is visible on dashboard."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Subscription form is absent on dashboard.";
    }
}
