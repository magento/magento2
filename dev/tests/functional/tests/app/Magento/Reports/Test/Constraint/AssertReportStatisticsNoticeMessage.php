<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\SalesReport;

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
     * @return void
     */
    public function processAssert(
        array $salesReport,
        SalesReport $salesReportPage
    ) {
        $timezone = new \DateTimeZone($_ENV['magento_timezone']);
        $initialDate = new \DateTime('now', $timezone);
        $this->salesReportPage = $salesReportPage;
        $this->searchInSalesReportGrid($salesReport);
        $displayedDate = new \DateTime($this->getLastUpdatedDate(), $timezone);
        $currentDate = new \DateTime('now', $timezone);

        \PHPUnit\Framework\Assert::assertTrue(
            $displayedDate->getTimestamp() > $initialDate->getTimestamp()
            && $displayedDate->getTimestamp() < $currentDate->getTimestamp(),
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
