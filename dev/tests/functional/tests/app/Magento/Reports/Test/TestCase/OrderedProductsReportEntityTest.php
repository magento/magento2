<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Reports\Test\Page\Adminhtml\OrderedProductsReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for OrderedProductsReportEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Place order
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Reports > Products > Ordered
 * 3. Select time range and report period
 * 4. Click "Refresh button"
 * 5. Perform all assertions
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-28200
 */
class OrderedProductsReportEntityTest extends Injectable
{
    /**
     * Ordered Products Report
     *
     * @var OrderedProductsReport
     */
    protected $orderedProducts;

    /**
     * Inject pages
     *
     * @param OrderedProductsReport $orderedProducts
     * @return void
     */
    public function __inject(OrderedProductsReport $orderedProducts)
    {
        $this->orderedProducts = $orderedProducts;
    }

    /**
     * Search order products report
     *
     * @param OrderInjectable $order
     * @param array $customersReport
     * @return void
     */
    public function test(OrderInjectable $order, array $customersReport)
    {
        // Preconditions
        $order->persist();

        // Steps
        $this->orderedProducts->open();
        $this->orderedProducts->getGridBlock()->searchAccounts($customersReport);
    }
}
