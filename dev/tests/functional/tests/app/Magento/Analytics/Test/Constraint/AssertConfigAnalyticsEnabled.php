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
 * Assert Advanced Reporting Service is enabled.
 */
class AssertConfigAnalyticsEnabled extends AbstractConstraint
{
    /**
     * Assert Advanced Reporting service is enabled.
     *
     * @param ConfigAnalytics $configAnalytics
     * @param OpenAnalyticsConfigStep $openAnalyticsConfigStep
     * @return void
     */
    public function processAssert(ConfigAnalytics $configAnalytics, OpenAnalyticsConfigStep $openAnalyticsConfigStep)
    {
        $openAnalyticsConfigStep->run();

<<<<<<< HEAD
        \PHPUnit_Framework_Assert::assertTrue(
            (bool)$configAnalytics->getAnalyticsForm()->isAnalyticsEnabled(),
            'Magento Advanced Reporting service is not enabled.'
        );
        \PHPUnit_Framework_Assert::assertEquals(
=======
        \PHPUnit\Framework\Assert::assertTrue(
            (bool)$configAnalytics->getAnalyticsForm()->isAnalyticsEnabled(),
            'Magento Advanced Reporting service is not enabled.'
        );

        \PHPUnit\Framework\Assert::assertEquals(
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            $configAnalytics->getAnalyticsForm()->getAnalyticsStatus(),
            'Subscription status: Pending',
            'Magento Advanced Reporting service subscription status is not pending.'
        );
<<<<<<< HEAD
        \PHPUnit_Framework_Assert::assertTrue(
            (bool)$configAnalytics->getAnalyticsForm()->getAnalyticsVerticalScope(),
            'Industry Data is not visible.'
        );
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Magento Advanced Reporting service is enabled and has Pending status';
    }
}
