<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Reports\Test\Page\Adminhtml\SalesInvoiceReport;
use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Preconditions:
 * 1. Open Backend
 * 2. Go to Reports > Sales > Invoiced
 * 3. Refresh statistic
 * 4. Configure filter
 * 5. Click "Show Report"
 * 6. Save/remember report result
 * 7. Create customer
 * 8. Place order
 * 9. Create Invoice
 * 10. Refresh statistic
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Reports > Sales > Invoiced
 * 3. Configure filter
 * 4. Click "Show Report"
 * 5. Perform all assertions
 *
 * @group Reports
 * @ZephyrId MAGETWO-29216
 */
class SalesInvoiceReportEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const STABLE = 'no';
    /* end tags */

    /**
     * Sales invoice report.
     *
     * @param FixtureFactory $fixtureFactory
     * @param SalesInvoiceReport $salesInvoiceReport
     * @param OrderInjectable $order
     * @param array $invoiceReport
     * @return array
     */
    public function test(
        FixtureFactory $fixtureFactory,
        SalesInvoiceReport $salesInvoiceReport,
        OrderInjectable $order,
        array $invoiceReport
    ) {
        // Preconditions
        $salesInvoiceReport->open();
        $salesInvoiceReport->getMessagesBlock()->clickLinkInMessage('notice', 'here');
        $salesInvoiceReport->getFilterForm()->viewsReport($invoiceReport);
        $salesInvoiceReport->getActionBlock()->showReport();
        $initialInvoiceResult = $salesInvoiceReport->getGridBlock()->getLastResult();
        $initialInvoiceTotalResult = $salesInvoiceReport->getGridBlock()->getTotalResult();
        $order->persist();
        $products = $order->getEntityId()['products'];
        $cart['data']['items'] = ['products' => $products];
        $cart = $fixtureFactory->createByCode('cart', $cart);
        $invoice = $this->objectManager->create(
            \Magento\Sales\Test\TestStep\CreateInvoiceStep::class,
            ['order' => $order, 'cart' => $cart]
        );
        $invoice->run();

        return [
            'initialInvoiceResult' => $initialInvoiceResult,
            'initialInvoiceTotalResult' => $initialInvoiceTotalResult
        ];
    }
}
