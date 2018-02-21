<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;

/**
 * Assert Advance Reporting Industry is set.
 */
class AssertIndustryIsSet extends AbstractConstraint
{
    /**
     * Assert Advance Reporting Industry is set
     *
     * @param ConfigAnalytics $configAnalytics
     * @param string $industry
     * @return void
     */
    public function processAssert(ConfigAnalytics $configAnalytics, $industry)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $industry,
            $configAnalytics->getAnalyticsForm()->getAnalyticsVertical(),
            $industry . 'industry is not selected'
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
            'Proper Magento Advanced Reporting industry is selected';
    }
}
