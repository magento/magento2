<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;

/**
 * Assert Analytics vertical scope is website in Stores > Configuration > General > Analytics > General menu.
 */
class AssertConfigAnalyticsVerticalScope extends AbstractConstraint
{
    /**
     * Assert Analytics vertical scope is website in Stores > Configuration > General > Analytics menu.
     *
     * @param ConfigAnalytics $configAnalytics
     */
    public function processAssert(ConfigAnalytics $configAnalytics)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            true,
            $configAnalytics->getAnalyticsForm()->getAnalyticsVerticalScope(),
            'Magento Analytics vertical scope is not website'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Magento Analytics vertical scope is website';
    }
}
