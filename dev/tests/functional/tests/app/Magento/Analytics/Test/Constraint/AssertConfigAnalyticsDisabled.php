<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Constraint;

use Magento\Analytics\Test\Page\Adminhtml\ConfigAnalytics;
use Magento\Analytics\Test\TestStep\OpenAnalyticsConfigStep;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert Advanced Reporting service is disabled
 */
class AssertConfigAnalyticsDisabled extends AbstractConstraint
{
    /**
     * Assert Advanced Reporting service is disabled.
     *
     * @param ConfigAnalytics $configAnalytics
     * @param OpenAnalyticsConfigStep $openAnalyticsConfigStep
     * @return void
     */
    public function processAssert(ConfigAnalytics $configAnalytics, OpenAnalyticsConfigStep $openAnalyticsConfigStep)
    {
        $openAnalyticsConfigStep->run();

<<<<<<< HEAD
        \PHPUnit_Framework_Assert::assertFalse(
            (bool)$configAnalytics->getAnalyticsForm()->isAnalyticsEnabled(),
            'Magento Advanced Reporting service is not disabled.'
        );
        \PHPUnit_Framework_Assert::assertEquals(
=======
        \PHPUnit\Framework\Assert::assertFalse(
            (bool)$configAnalytics->getAnalyticsForm()->isAnalyticsEnabled(),
            'Magento Advanced Reporting service is not disabled.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
            $configAnalytics->getAnalyticsForm()->getAnalyticsStatus(),
            'Subscription status: Disabled',
            'Magento Advanced Reporting service subscription status is not disabled.'
        );
<<<<<<< HEAD
        \PHPUnit_Framework_Assert::assertFalse(
            (bool)$configAnalytics->getAnalyticsForm()->getAnalyticsVerticalScope(),
            'Industry Data is visible.'
        );
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Magento Advanced Reporting service is disabled and has Disabled status.';
    }
}
