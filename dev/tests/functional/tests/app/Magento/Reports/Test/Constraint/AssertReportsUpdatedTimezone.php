<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\Statistics;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that reports 'Updated' values are displayed in date/time in Default Config timezone.
 */
class AssertReportsUpdatedTimezone extends AbstractConstraint
{
    /**
     * Assert that reports 'Updated' values are displayed in date/time in Default Config timezone.
     *
     * @param Statistics $reportStatistics
     * @return void
     */
    public function processAssert(Statistics $reportStatistics)
    {
        $reportStatistics->open();
        $dates = $reportStatistics->getGridBlock()->getRowsData(['updated_at']);
        $currentDate = new \DateTime('now', new \DateTimeZone($_ENV['magento_timezone']));
        $currentDate = date('M j, Y, g', $currentDate->getTimestamp());
        foreach ($dates as $date) {
            \PHPUnit_Framework_Assert::assertContains(
                $currentDate,
                $date['updated_at'],
                "Reports 'Updated' column values are displayed in an incorrect timezone."
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Reports 'Updated' column values are displayed in the correct timezone.";
    }
}
