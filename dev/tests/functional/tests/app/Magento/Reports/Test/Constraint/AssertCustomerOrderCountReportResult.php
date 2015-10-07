<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Reports\Test\Page\Adminhtml\CustomerOrdersReport;

/**
 * Assert OrderCountReport grid for all params.
 */
class AssertCustomerOrderCountReportResult extends AbstractAssertCustomerOrderReportResult
{
    /**
     * Assert OrderCountReport grid for all params.
     *
     * @param CustomerOrdersReport $customerOrdersReport
     * @param Customer $customer
     * @param array $columns
     * @param array $report
     * @return void
     */
    public function processAssert(
        CustomerOrdersReport $customerOrdersReport,
        Customer $customer,
        array $columns,
        array $report
    ) {
        $filter = $this->prepareFilter($customer, $columns, $report);

        \PHPUnit_Framework_Assert::assertTrue(
            $customerOrdersReport->getGridBlock()->isRowVisible($filter, false),
            'Order does not present in count grid.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order count is present in count grid.';
    }
}
