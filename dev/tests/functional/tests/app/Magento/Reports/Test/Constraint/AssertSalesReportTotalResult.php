<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\SalesReport;
use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Class AssertSalesReportTotalResult
 * Assert that total sales info in report grid is actual
 */
class AssertSalesReportTotalResult extends AbstractAssertSalesReportResult
{
    /**
     * Assert that total sales info in report grid is actual
     *
     * @param OrderInjectable $order
     * @param array $salesReport
     * @param array $initialSalesTotalResult
     * @param SalesReport $salesReportPage
     * @return void
     */
    public function processAssert(
        OrderInjectable $order,
        array $salesReport,
        array $initialSalesTotalResult,
        SalesReport $salesReportPage
    ) {
        $this->salesReportPage = $salesReportPage;
        $this->order = $order;
        $this->searchInSalesReportGrid($salesReport);
        $salesResult = $this->prepareSalesResult($salesReportPage->getGridBlock()->getTotalResult());
        $prepareInitialResult = $this->prepareSalesResult($this->prepareExpectedResult($initialSalesTotalResult));
        \PHPUnit_Framework_Assert::assertEquals(
            $prepareInitialResult,
            $salesResult,
            "Grand total Sales result is not correct."
        );
    }

    /**
     * Prepare sales result.
     *
     * @param array $salesResult
     * @return array
     */
    protected function prepareSalesResult($salesResult)
    {
        $data = [];
        foreach ($salesResult as $key => $result) {
            $data[$key] = floatval($result);
        }

        return $data;
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Sales report grand total result contains actual data.';
    }
}
