<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Advertisement\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that Advertisement Popup is visible on dashboard
 */
class AssertAdvertisementPopupExist extends AbstractConstraint
{
    /**
     * Assert that advertisement Popup is visible on dashboard
     *
     * @param Dashboard $dashboard
     * @return void
     */
    public function processAssert(Dashboard $dashboard)
    {
        \PHPUnit_Framework_Assert::assertTrue(
            $dashboard->getAdvertisementBlock()->isVisible(),
            "Advertisement Popup is absent on dashboard."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Advertisement Popup is visible on dashboard.";
    }
}
