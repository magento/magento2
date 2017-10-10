<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;

/**
 * Assert Advanced Reporting industry scope is website in Stores.
 */
class AssertConfigAnalyticsIndustryScope extends AbstractConstraint
{
    /**
     * Assert Advanced Reporting industry scope is website in Stores.
     *
     * @param ConfigAnalytics $configAnalytics
     */
    public function processAssert(ConfigAnalytics $configAnalytics)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            true,
            $configAnalytics->getAnalyticsForm()->getAnalyticsVerticalScope(),
            'Magento Advanced Reporting industry scope is not website'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Magento Advanced Reporting industry scope is website';
    }
}
