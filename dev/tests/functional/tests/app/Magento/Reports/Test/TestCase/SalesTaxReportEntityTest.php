<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Reports\Test\Page\Adminhtml\SalesTaxReport;
use Magento\Reports\Test\Page\Adminhtml\Statistics;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceNew;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleNew;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Product is created.
 * 2. Customer is created.
 * 3. Tax Rule is created.
 * 4. Order is placed.
 * 5. Refresh statistic.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Go to Reports > Sales > Tax.
 * 3. Fill data from dataset.
 * 4. Click "Show report".
 * 5. Perform all assertions.
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-28515
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SalesTaxReportEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Order index page.
     *
     * @var OrderIndex
     */
    protected $orderIndex;

    /**
     * Order invoice new page.
     *
     * @var OrderInvoiceNew
     */
    protected $orderInvoiceNew;

    /**
     * Sales tax report page.
     *
     * @var SalesTaxReport
     */
    protected $salesTaxReport;

    /**
     * Order view page.
     *
     * @var SalesOrderView
     */
    protected $salesOrderView;

    /**
     * Report statistic page.
     *
     * @var Statistics
     */
    protected $reportStatistic;

    /**
     * Tax Rule grid page.
     *
     * @var TaxRuleIndex
     */
    protected $taxRuleIndexPage;

    /**
     * Tax Rule new and edit page.
     *
     * @var TaxRuleNew
     */
    protected $taxRuleNewPage;

    /**
     * Tax Rule fixture.
     *
     * @var TaxRule
     */
    protected $taxRule;

    /**
     * Delete all tax rules.
     *
     * @return void
     */
    public function __prepare()
    {
        $deleteTaxRule = $this->objectManager->create('Magento\Tax\Test\TestStep\DeleteAllTaxRulesStep');
        $deleteTaxRule->run();
    }

    /**
     * Injection data.
     *
     * @param OrderIndex $orderIndex
     * @param OrderInvoiceNew $orderInvoiceNew
     * @param SalesOrderView $salesOrderView
     * @param Statistics $reportStatistic
     * @param SalesTaxReport $salesTaxReport
     * @param TaxRuleIndex $taxRuleIndexPage
     * @param TaxRuleNew $taxRuleNewPage
     * @return void
     */
    public function __inject(
        OrderIndex $orderIndex,
        OrderInvoiceNew $orderInvoiceNew,
        SalesOrderView $salesOrderView,
        Statistics $reportStatistic,
        SalesTaxReport $salesTaxReport,
        TaxRuleIndex $taxRuleIndexPage,
        TaxRuleNew $taxRuleNewPage
    ) {
        $this->orderIndex = $orderIndex;
        $this->orderInvoiceNew = $orderInvoiceNew;
        $this->salesOrderView = $salesOrderView;
        $this->reportStatistic = $reportStatistic;
        $this->salesTaxReport = $salesTaxReport;
        $this->taxRuleIndexPage = $taxRuleIndexPage;
        $this->taxRuleNewPage = $taxRuleNewPage;
    }

    /**
     * Create tax report entity.
     *
     * @param OrderInjectable $order
     * @param TaxRule $taxRule
     * @param array $report
     * @param string $orderSteps
     * @return void
     */
    public function test(
        OrderInjectable $order,
        TaxRule $taxRule,
        array $report,
        $orderSteps
    ) {
        // Precondition
        $taxRule->persist();
        $this->taxRule = $taxRule;
        $order->persist();
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        $this->processOrder($orderSteps, $order);
        $this->reportStatistic->open();
        $this->reportStatistic->getGridBlock()->massaction(
            [['report' => 'Tax']],
            'Refresh Statistics for the Last Day',
            true
        );

        // Steps
        $this->salesTaxReport->open();
        $this->salesTaxReport->getFilterBlock()->viewsReport($report);
        $this->salesTaxReport->getActionBlock()->showReport();
    }

    /**
     * Process order to corresponded status.
     *
     * @param string $orderSteps
     * @param OrderInjectable $order
     * @return void
     */
    protected function processOrder($orderSteps, OrderInjectable $order)
    {
        if ($orderSteps === '-') {
            return;
        }
        $orderStatus = explode(',', $orderSteps);
        foreach ($orderStatus as $orderStep) {
            $this->objectManager->create(
                'Magento\Sales\Test\TestStep\\Create' . ucfirst(trim($orderStep)) . 'Step',
                ['order' => $order]
            )->run();
        }
    }

    /**
     * Delete all tax rules after test.
     *
     * @return void
     */
    public function tearDown()
    {
        $deleteTaxRule = $this->objectManager->create('Magento\Tax\Test\TestStep\DeleteAllTaxRulesStep');
        $deleteTaxRule->run();
    }
}
