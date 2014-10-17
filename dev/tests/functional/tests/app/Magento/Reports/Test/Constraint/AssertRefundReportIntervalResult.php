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

use Magento\Reports\Test\Page\Adminhtml\RefundsReport;
use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Class AssertRefundReportIntervalResult
 * Assert Credit Memo info in report grid
 */
class AssertRefundReportIntervalResult extends AbstractAssertSalesReportResult
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
        $expectedOrderData['refunded'] += $this->order->getPrice()[0]['grand_order_total'];
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
