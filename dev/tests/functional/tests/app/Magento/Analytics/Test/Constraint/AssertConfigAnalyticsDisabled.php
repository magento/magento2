<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;

/**
 * Assert Analytics is disabled in Stores>Configuration>General>Analytics->General menu.
 */
class AssertConfigAnalyticsDisabled extends AbstractConstraint
{
    /**
     * Assert Analytics is disabled in Stores>Configuration>General>Analytics menu.
     * @param ConfigAnalytics $configAnalytics
     */
    public function processAssert(ConfigAnalytics $configAnalytics)
    {
        \PHPUnit_Framework_Assert::assertFalse(
            (bool)$configAnalytics->getAnalyticsForm()->isAnalyticsEnabled(),
            'Magento Analytics is disabled'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Magento Analytics is not disabled in Stores>Configuration>General>Analytics->General menu';
    }
}
