<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;

/**
 * Assert Analytics status is pending in Stores>Configuration>General>Analytics->General menu.
 */
class AssertConfigAnalyticsStatusPending extends AbstractConstraint
{
    /**
     * Assert Analytics status is Pending in Stores>Configuration>General>Analytics menu.
     * @param ConfigAnalytics $configAnalytics
     */
    public function processAssert(ConfigAnalytics $configAnalytics)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $configAnalytics->getAnalyticsForm()->getAnalyticsStatus(),
            'Subscription status: Pending',
            'Magento Analytics is enabled'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Magento Analytics is not pending in Stores>Configuration>General>Analytics->General menu';
    }
}
