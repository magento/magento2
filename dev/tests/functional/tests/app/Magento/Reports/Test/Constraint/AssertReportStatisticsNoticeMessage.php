<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\SalesReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use DateTime;

/**
 * Assert that message in Sales Reports Pages displays correct date/time.
 */
class AssertReportStatisticsNoticeMessage extends AbstractAssertSalesReportResult
{
    /**
     * Last updated string prefix.
     *
     * @var string
     */
    private $lastUpdatedPrefix = 'Last updated: ';

    /**
     * Assert that message in Sales Reports Page displays correct date/time in Default Config timezone.
     *
     * @param array $salesReport
     * @param SalesReport $salesReportPage
     * @param DateTime $currentDate
     * @return void
     */
    public function processAssert(
        array $salesReport,
        SalesReport $salesReportPage,
        DateTime $currentDate
    ) {
        $this->salesReportPage = $salesReportPage;
        $this->searchInSalesReportGrid($salesReport);
        $date = $this->getLastUpdatedDate();
        $currentDateTime = $currentDate->format('M j, Y, g');
        $displayedDateTime = date('M j, Y, g', strtotime($date));
        \PHPUnit_Framework_Assert::assertEquals(
            $currentDateTime,
            $displayedDateTime,
            "Message in Sales Reports Page is displayed in an incorrect timezone."
        );
    }

    /**
     * Get last updated date value.
     *
     * @return string
     */
    private function getLastUpdatedDate()
    {
        $result = '';

        foreach ($this->salesReportPage->getMessagesBlock()->getNoticeMessages() as $message) {
            if (strpos($message, $this->lastUpdatedPrefix) === 0) {
                $messages = explode('.', $message);
                $message = array_shift($messages);
                $result = trim($message, $this->lastUpdatedPrefix);
                break;
            }
        }

        return $result;
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Message in Sales Reports Page displays correct date/time in the correct timezone.';
    }
}
