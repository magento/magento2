<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Reports\Test\Page\Adminhtml\SalesTaxReport;
use Magento\Reports\Test\Page\Adminhtml\Statistics;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceNew;
use Magento\Sales\Test\Page\Adminhtml\OrderView;
use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleIndex;
use Magento\Tax\Test\Page\Adminhtml\TaxRuleNew;
use Mtf\TestCase\Injectable;

/**
 * Test Flow:
 *
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
 * 3. Fill data from dataSet.
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
     * @var OrderView
     */
    protected $orderView;

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
     * @param OrderView $orderView
     * @param Statistics $reportStatistic
     * @param SalesTaxReport $salesTaxReport
     * @param TaxRuleIndex $taxRuleIndexPage
     * @param TaxRuleNew $taxRuleNewPage
     * @return void
     */
    public function __inject(
        OrderIndex $orderIndex,
        OrderInvoiceNew $orderInvoiceNew,
        OrderView $orderView,
        Statistics $reportStatistic,
        SalesTaxReport $salesTaxReport,
        TaxRuleIndex $taxRuleIndexPage,
        TaxRuleNew $taxRuleNewPage
    ) {
        $this->orderIndex = $orderIndex;
        $this->orderInvoiceNew = $orderInvoiceNew;
        $this->orderView = $orderView;
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
     * @param string $orderStatus
     * @param string $invoice
     * @return void
     */
    public function test(
        OrderInjectable $order,
        TaxRule $taxRule,
        array $report,
        $orderStatus,
        $invoice
    ) {
        // Precondition
        $taxRule->persist();
        $this->taxRule = $taxRule;
        $order->persist();
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        if ($orderStatus !== 'Pending') {
            $createInvoice = $this->objectManager->create(
                'Magento\Sales\Test\TestStep\CreateInvoiceStep',
                ['order' => $order, 'data' => $invoice]
            );
            $createInvoice->run();
        }
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
