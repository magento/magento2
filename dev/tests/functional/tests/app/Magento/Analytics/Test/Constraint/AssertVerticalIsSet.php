<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;

/**
 * Assert Analytics Vertical is set in Stores > Configuration > General > Analytics > General menu.
 */
class AssertVerticalIsSet extends AbstractConstraint
{
    /**
     * Assert Analytics Vertical is set in Stores > Configuration > General > Analytics > General menu.
     *
     * @param ConfigAnalytics $configAnalytics
     * @param string $vertical
     * @return void
     */
    public function processAssert(ConfigAnalytics $configAnalytics, $vertical)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $vertical,
            $configAnalytics->getAnalyticsForm()->getAnalyticsVertical(),
            $vertical . 'vertical is not selected'
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
            'Proper Magento Analytics vertical is selected';
    }
}
