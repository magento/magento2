<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

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
        $salesResult = $salesReportPage->getGridBlock()->getTotalResult();
        $prepareInitialResult = $this->prepareExpectedResult($initialSalesTotalResult);
        \PHPUnit_Framework_Assert::assertEquals(
            $prepareInitialResult,
            $salesResult,
            "Grand total Sales result is not correct."
        );
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
