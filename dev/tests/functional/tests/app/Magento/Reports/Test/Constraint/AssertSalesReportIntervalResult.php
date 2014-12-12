<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Reports\Test\Page\Adminhtml\SalesReport;
use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Class AssertSalesReportIntervalResult
 * Assert that sales info in report grid is actual
 */
class AssertSalesReportIntervalResult extends AbstractAssertSalesReportResult
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that sales info in report grid is actual
     *
     * @param OrderInjectable $order
     * @param array $salesReport
     * @param array $initialSalesResult
     * @param SalesReport $salesReportPage
     * @return void
     */
    public function processAssert(
        OrderInjectable $order,
        array $salesReport,
        array $initialSalesResult,
        SalesReport $salesReportPage
    ) {
        $this->salesReportPage = $salesReportPage;
        $this->order = $order;
        $this->searchInSalesReportGrid($salesReport);
        $salesResult = $salesReportPage->getGridBlock()->getLastResult();
        $prepareInitialResult = $this->prepareExpectedResult($initialSalesResult);
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
        return 'Sales report interval result contains actual data.';
    }
}
