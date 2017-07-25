<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Reports\Test\Page\Adminhtml\SalesReport;
use Magento\Sales\Test\Fixture\OrderInjectable;

/**
 * Preconditions:
 * 1. Open Backend
 * 2. Go to Reports > Sales > Orders
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
 * 2. Go to Reports > Sales > Orders
 * 3. Configure filter
 * 4. Click "Show Report"
 * 5. Perform all assertions
 *
 * @group Reports
 * @ZephyrId MAGETWO-29136
 */
class SalesOrderReportEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Sales Report page.
     *
     * @var SalesReport
     */
    protected $salesReport;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Inject page.
     *
     * @param FixtureFactory $fixtureFactory
     * @param SalesReport $salesReport
     * @return void
     */
    public function __inject(FixtureFactory $fixtureFactory, SalesReport $salesReport)
    {
        $this->salesReport = $salesReport;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Sales order report.
     *
     * @param OrderInjectable $order
     * @param array $salesReport
     * @return array
     */
    public function test(OrderInjectable $order, array $salesReport)
    {
        // Preconditions
        $this->salesReport->open();
        $this->salesReport->getMessagesBlock()->clickLinkInMessage('notice', 'here');
        $this->salesReport->getFilterBlock()->viewsReport($salesReport);
        $this->salesReport->getActionBlock()->showReport();
        $initialSalesResult = $this->salesReport->getGridBlock()->getLastResult();
        $initialSalesTotalResult = $this->salesReport->getGridBlock()->getTotalResult();

        $order->persist();
        $products = $order->getEntityId()['products'];
        $cart['data']['items'] = ['products' => $products];
        $cart = $this->fixtureFactory->createByCode('cart', $cart);
        $invoice = $this->objectManager->create(
            \Magento\Sales\Test\TestStep\CreateInvoiceStep::class,
            ['order' => $order, 'cart' => $cart]
        );
        $invoice->run();

        return ['initialSalesResult' => $initialSalesResult, 'initialSalesTotalResult' => $initialSalesTotalResult];
    }
}
