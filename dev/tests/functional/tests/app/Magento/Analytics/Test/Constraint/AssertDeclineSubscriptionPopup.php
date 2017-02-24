<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Verify that admin user chose to decline subscription in Analytics pop-up.
 */
class AssertDeclineSubscriptionPopup extends AbstractConstraint
{
    /**
     * Verify that admin user chose to decline subscription in Analytics pop-up.
     *
     * @param Dashboard $dashboard
     * @return void
     */
    public function processAssert(Dashboard $dashboard)
    {
        $dashboard->open();
        $dashboard->getSubscriptionBlock()->enableCheckbox();
        $dashboard->getModalBlock()->dismissWarning();
        \PHPUnit_Framework_Assert::assertFalse(
            $dashboard->getSubscriptionBlock()->isVisible(),
            'Subscription pop-up was not declined'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Subscription pop-up was declined';
    }
}
