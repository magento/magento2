<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\RefundsReport;
use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Class AssertRefundReportIntervalResult
 * Assert Credit Memo info in report grid
 */
class AssertRefundReportIntervalResult extends AbstractAssertSalesReportResult
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert Credit Memo info in report: Refunds Orders, Total Refunded
     *
     * @param OrderInjectable $order
     * @param array $refundsReport
     * @param array $initialRefundsResult
     * @param RefundsReport $refundsReportPage
     * @return void
     */
    public function processAssert(
        OrderInjectable $order,
        array $refundsReport,
        array $initialRefundsResult,
        RefundsReport $refundsReportPage
    ) {
        $this->salesReportPage = $refundsReportPage;
        $this->order = $order;
        $this->searchInSalesReportGrid($refundsReport);
        $salesResult = $refundsReportPage->getGridBlock()->getLastResult();
        $prepareInitialResult = $this->prepareExpectedResult($initialRefundsResult);
        \PHPUnit_Framework_Assert::assertEquals(
            $prepareInitialResult,
            $salesResult,
            "Refund total Sales result is not correct."
        );
    }

    /**
     * Prepare expected result
     *
     * @param array $expectedOrderData
     * @return array
     */
    protected function prepareExpectedResult(array $expectedOrderData)
    {
        ++$expectedOrderData['orders_count'];
        $expectedOrderData['refunded'] += $this->order->getPrice()['invoice'][0]['grand_order_total'];
        return $expectedOrderData;
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Refund report interval result contains actual data.';
    }
}
