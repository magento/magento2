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
 * Assert sending data to the Analytics is restored.
 */
class AssertConfigAnalyticsSendingTimeAndZone extends AbstractConstraint
{
    /**
     * @param ConfigAnalytics $configAnalytics
     * @param OpenAnalyticsConfigStep $openAnalyticsConfigStep
     * @param string $hh
     * @param string $mm
     * @return void
     */
    public function processAssert(
        ConfigAnalytics $configAnalytics,
        OpenAnalyticsConfigStep $openAnalyticsConfigStep,
        $hh,
        $mm
    ) {
        $openAnalyticsConfigStep->run();

        \PHPUnit_Framework_Assert::assertEquals(
            'Eastern European Standard Time (Europe/Kiev)',
            $configAnalytics->getAnalyticsForm()->getTimeZone()
        );

        \PHPUnit_Framework_Assert::assertEquals(
            sprintf('%s, %s, 00', $hh, $mm),
            $configAnalytics->getAnalyticsForm()->getTimeOfDayToSendDate()
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Time and TimeZone are correct!';
    }
}
