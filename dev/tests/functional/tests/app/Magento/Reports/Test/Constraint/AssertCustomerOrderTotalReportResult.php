<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
