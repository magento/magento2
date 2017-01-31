<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;

/**
 * Assert Analytics Vertical ise set in Stores > Configuration > General > Analytics > General menu.
 */
class AssertVerticalIsSet extends AbstractConstraint
{
    /**
     * Assert Analytics Vertical ise set in Stores > Configuration > General > Analytics > General menu.
     *
     * @param ConfigAnalytics $configAnalytics
     * @return void
     */
    public function processAssert(ConfigAnalytics $configAnalytics)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            'Apps and Games',
            $configAnalytics->getAnalyticsForm()->getAnalyticsVertical(),
            'Apps and Games vertical is not selected'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return
            'Magento Analytics vertical Apps and Games is selected';
    }
}
