<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Reports\Test\Page\Adminhtml\SalesCouponReportView;
use Magento\Reports\Test\Page\Adminhtml\Statistics;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceNew;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create order with coupon
 * 2. Make invoice for this order
 * 3. Refresh statistic
 *
 * Steps:
 * 1. Login to backend
 * 2. Go to Reports > Sales > Coupons
 * 3. Select time range, report period etc
 * 4. Click "Show report"
 * 5. Perform all assertions
 *
 * @group Reports
 * @ZephyrId MAGETWO-28190
 */
class SalesCouponReportEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const STABLE = 'no';
    /* end tags */

    /**
     * Order index page
     *
     * @var OrderIndex
     */
    protected $orderIndex;

    /**
     * Order invoice new page
     *
     * @var OrderInvoiceNew
     */
    protected $orderInvoiceNew;

    /**
     * Sales coupon report view page
     *
     * @var SalesCouponReportView
     */
    protected $salesCouponReportView;

    /**
     * Order view page
     *
     * @var SalesOrderView
     */
    protected $salesOrderView;

    /**
     * Report statistic page
     *
     * @var Statistics
     */
    protected $reportStatistic;

    /**
     * Injection data
     *
     * @param OrderIndex $orderIndex
     * @param OrderInvoiceNew $orderInvoiceNew
     * @param SalesCouponReportView $salesCouponReportView
     * @param SalesOrderView $salesOrderView
     * @param Statistics $reportStatistic
     * @return void
     */
    public function __inject(
        OrderIndex $orderIndex,
        OrderInvoiceNew $orderInvoiceNew,
        SalesCouponReportView $salesCouponReportView,
        SalesOrderView $salesOrderView,
        Statistics $reportStatistic
    ) {
        $this->orderIndex = $orderIndex;
        $this->orderInvoiceNew = $orderInvoiceNew;
        $this->salesCouponReportView = $salesCouponReportView;
        $this->salesOrderView = $salesOrderView;
        $this->reportStatistic = $reportStatistic;
    }

    /**
     * Sales coupon report
     *
     * @param OrderInjectable $order
     * @param array $viewsReport
     * @return void
     */
    public function test(OrderInjectable $order, array $viewsReport)
    {
        // Precondition
        $order->persist();
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        $this->salesOrderView->getPageActions()->invoice();
        $this->orderInvoiceNew->getTotalsBlock()->submit();
        $this->reportStatistic->open();
        $this->reportStatistic->getGridBlock()->massaction(
            [['report' => 'Coupons']],
            'Refresh Statistics for the Last Day',
            true
        );

        // Steps
        $this->salesCouponReportView->open();
        $ruleName = $order->getCouponCode()->getName();
        if (isset($viewsReport['rules_list'])) {
            $viewsReport['rules_list'] = str_replace('%rule_name%', $ruleName, $viewsReport['rules_list']);
        }
        $this->salesCouponReportView->getFilterBlock()->viewsReport($viewsReport);
        $this->salesCouponReportView->getActionBlock()->showReport();
    }
}
