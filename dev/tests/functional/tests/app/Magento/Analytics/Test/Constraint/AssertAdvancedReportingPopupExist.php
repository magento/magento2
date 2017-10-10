<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Advanced Reporting Popup is visible on dashboard
 */
class AssertAdvancedReportingPopupExist extends AbstractConstraint
{
    /**
     * Assert that Advanced Reporting Popup is visible on dashboard
     *
     * @param Dashboard $dashboard
     * @return void
     */
    public function processAssert(Dashboard $dashboard)
    {
        \PHPUnit_Framework_Assert::assertTrue(
            $dashboard->getAdvancedReportingBlock()->isVisible(),
            "Advanced Reporting Popup is absent on dashboard."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Advanced Reporting Popup is visible on dashboard.";
    }
}
