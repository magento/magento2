<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Verify that admin user chose to skip subscription in Analytics pop-up.
 */
class AssertSkipSubscriptionPopup extends AbstractConstraint
{
    /**
     * Verify that admin user chose to skip subscription in Analytics pop-up.
     *
     * @param Dashboard $dashboard
     * @return void
     */
    public function processAssert(Dashboard $dashboard)
    {
        $dashboard->open();
        $dashboard->getSubscriptionBlock()->disableCheckbox();
        $dashboard->getModalBlock()->dismissWarning();
        \PHPUnit_Framework_Assert::assertFalse(
            $dashboard->getSubscriptionBlock()->isVisible(),
            'Subscription pop-up was not skipped'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Subscription pop-up was skipped';
    }
}
