<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Reports\Test\Page\Adminhtml\RefundsReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Open Backend
 * 2. Go to Reports > Sales > Refunds
 * 3. Refresh statistic
 * 4. Configure filter
 * 5. Click "Show Report"
 * 6. Save/remember report result
 * 7. Place order
 * 8. Create Invoice
 * 9. Refresh statistic
 *
 * Steps:
 * 1. Go to backend
 * 2. Go to Reports > Sales > Refunds
 * 3. Fill data from dataset
 * 4. Click button Show Report
 * 5. Perform Asserts
 *
 * @group Reports
 * @ZephyrId MAGETWO-29348
 */
class SalesRefundsReportEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Refunds report page.
     *
     * @var RefundsReport
     */
    protected $refundsReport;

    /**
     * Inject pages.
     *
     * @param RefundsReport $refundsReport
     * @return void
     */
    public function __inject(RefundsReport $refundsReport)
    {
        $this->refundsReport = $refundsReport;
    }

    /**
     * Refunds report.
     *
     * @param OrderInjectable $order
     * @param array $refundsReport
     * @return array
     */
    public function test(OrderInjectable $order, array $refundsReport)
    {
        // Preconditions
        $this->refundsReport->open();
        $this->refundsReport->getMessagesBlock()->clickLinkInMessage('notice', 'here');
        $this->refundsReport->getFilterBlock()->viewsReport($refundsReport);
        $this->refundsReport->getActionBlock()->showReport();
        $initialRefundsResult = $this->refundsReport->getGridBlock()->getLastResult();

        $order->persist();
        $invoice = $this->objectManager->create(
            \Magento\Sales\Test\TestStep\CreateInvoiceStep::class,
            ['order' => $order]
        );
        $invoice->run();
        $creditMemo = $this->objectManager->create(
            \Magento\Sales\Test\TestStep\CreateCreditMemoStep::class,
            ['order' => $order]
        );
        $creditMemo->run();

        return ['initialRefundsResult' => $initialRefundsResult];
    }
}
