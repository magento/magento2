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

namespace Magento\Reports\Test\TestCase;

use Magento\Reports\Test\Page\Adminhtml\RefundsReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for SalesRefundsReportEntity
 *
 * Test Flow:
 *
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
 * 3. Fill data from dataSet
 * 4. Click button Show Report
 * 5. Perform Asserts
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-29348
 */
class SalesRefundsReportEntityTest extends Injectable
{
    /**
     * Refunds report page
     *
     * @var RefundsReport
     */
    protected $refundsReport;

    /**
     * Inject pages
     *
     * @param RefundsReport $refundsReport
     * @return void
     */
    public function __inject(RefundsReport $refundsReport)
    {
        $this->refundsReport = $refundsReport;
    }

    /**
     * Refunds report
     *
     * @param OrderInjectable $order
     * @param array $refundsReport
     * @return array
     */
    public function test(OrderInjectable $order, array $refundsReport)
    {
        // Preconditions
        $this->refundsReport->open();
        $this->refundsReport->getMessagesBlock()->clickLinkInMessages('notice', 'here');
        $this->refundsReport->getFilterBlock()->viewsReport($refundsReport);
        $this->refundsReport->getActionBlock()->showReport();
        $initialRefundsResult = $this->refundsReport->getGridBlock()->getLastResult();

        $order->persist();
        $invoice = $this->objectManager->create('Magento\Sales\Test\TestStep\CreateInvoiceStep', ['order' => $order]);
        $invoice->run();
        $creditMemo = $this->objectManager->create(
            'Magento\Sales\Test\TestStep\CreateCreditMemoStep',
            ['order' => $order]
        );
        $creditMemo->run();

        return ['initialRefundsResult' => $initialRefundsResult];
    }
}
