<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Class AssertInvoiceReportIntervalResult
 * Assert that invoice info in report grid is actual
 */
class AssertInvoiceReportIntervalResult extends AbstractAssertInvoiceReportResult
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
     * @param array $invoiceReport
     * @param array $initialInvoiceResult
     * @return void
     */
    public function processAssert(OrderInjectable $order, array $invoiceReport, array $initialInvoiceResult)
    {
        $this->order = $order;
        $this->searchInInvoiceReportGrid($invoiceReport);
        $invoiceResult = $this->salesInvoiceReport->getGridBlock()->getLastResult();
        $prepareInitialResult = $this->prepareExpectedResult($initialInvoiceResult);
        \PHPUnit_Framework_Assert::assertEquals(
            $prepareInitialResult,
            $invoiceResult,
            "Invoice report interval result not contains actual data."
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Invoice report interval result contains actual data.';
    }
}
