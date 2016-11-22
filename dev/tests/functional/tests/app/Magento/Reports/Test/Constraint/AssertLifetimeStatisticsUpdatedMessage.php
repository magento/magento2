<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\Statistics;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that correct success message is displayed after refreshing reports lifetime statistics.
 */
class AssertLifetimeStatisticsUpdatedMessage extends AbstractConstraint
{
    const LIFETIME_STATISTICS_UPDATED_MESSAGE = 'You refreshed lifetime statistics.';
    /**
     * Assert that correct success message is displayed after refreshing reports lifetime statistics.
     *
     * @param Statistics $reportStatistics
     * @return void
     */
    public function processAssert(Statistics $reportStatistics)
    {
        $successMessage = $reportStatistics->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::LIFETIME_STATISTICS_UPDATED_MESSAGE,
            $successMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::LIFETIME_STATISTICS_UPDATED_MESSAGE
            . "\nActual: " . $successMessage
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Correct message is displayed after refreshing lifetime statistics.";
    }
}
