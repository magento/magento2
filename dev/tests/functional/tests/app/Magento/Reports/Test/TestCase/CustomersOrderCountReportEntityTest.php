<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Reports\Test\Page\Adminhtml\CustomerOrdersReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for OrderCountReportEntity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create customer
 * 2. Create orders for customer
 *
 * Steps:
 * 1. Login to backend
 * 2. Open Reports > Customer > Order Count
 * 3. Fill data from dataSet
 * 4. Click button Refresh
 * 5. Perform all assertions
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-28521
 */
class CustomersOrderCountReportEntityTest extends Injectable
{
    /**
     * Order count report page
     *
     * @var CustomerOrdersReport
     */
    protected $customerOrdersReport;

    /**
     * Inject page
     *
     * @param CustomerOrdersReport $customerOrdersReport
     * @return void
     */
    public function __inject(CustomerOrdersReport $customerOrdersReport)
    {
        $this->customerOrdersReport = $customerOrdersReport;
    }

    /**
     * Order count report view
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
        $this->customerOrdersReport->open();
        $this->customerOrdersReport->getFilterBlock()->viewsReport($report);
        $this->customerOrdersReport->getFilterBlock()->refreshFilter();

        return['customer' => $customer];
    }
}
