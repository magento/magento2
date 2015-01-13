<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Reports\Test\Page\Adminhtml\CustomerTotalsReport;

/**
 * Class AssertCustomerOrderTotalReportResult
 * Assert OrderTotalReport grid for all params
 */
class AssertCustomerOrderTotalReportResult extends AbstractAssertCustomerOrderReportResult
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert OrderTotalReport grid for all params
     *
     * @param CustomerTotalsReport $customerTotalsReport
     * @param CustomerInjectable $customer
     * @param array $columns
     * @param array $report
     * @return void
     */
    public function processAssert(
        CustomerTotalsReport $customerTotalsReport,
        CustomerInjectable $customer,
        array $columns,
        array $report
    ) {
        $filter = $this->prepareFilter($customer, $columns, $report);

        \PHPUnit_Framework_Assert::assertTrue(
            $customerTotalsReport->getGridBlock()->isRowVisible($filter),
            'Order does not present in report grid.'
        );
    }

    /**
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Order total is present in reports grid.';
    }
}
