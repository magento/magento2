<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Reports\Test\Page\Adminhtml\CustomerTotalsReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for OrderTotalReportEntity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create customer
 * 2. Create orders for customer
 *
 * Steps:
 * 1. Login to backend
 * 2. Open Reports > Customer > Order Total
 * 3. Fill data from dataSet
 * 4. Click button Refresh
 * 5. Perform all assertions
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-28358
 */
class CustomersOrderTotalReportEntityTest extends Injectable
{
    /**
     * Order total report page
     *
     * @var CustomerTotalsReport
     */
    protected $customerTotalsReport;

    /**
     * Inject page
     *
     * @param CustomerTotalsReport $customerTotalsReport
     * @return void
     */
    public function __inject(CustomerTotalsReport $customerTotalsReport)
    {
        $this->customerTotalsReport = $customerTotalsReport;
    }

    /**
     * Order total report view
     *
     * @param CustomerInjectable $customer
     * @param string $orders
     * @param array $report
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function test(CustomerInjectable $customer, $orders, array $report, FixtureFactory $fixtureFactory)
    {
        // Precondition
        $customer->persist();
        $orders = explode(',', $orders);
        foreach ($orders as $order) {
            $order = $fixtureFactory->createByCode(
                'orderInjectable',
                ['dataSet' => $order, 'data' => ['customer_id' => ['customer' => $customer]]]
            );
            $order->persist();
        }

        // Steps
        $this->customerTotalsReport->open();
        $this->customerTotalsReport->getFilterBlock()->viewsReport($report);
        $this->customerTotalsReport->getFilterBlock()->refreshFilter();

        return['customer' => $customer];
    }
}
