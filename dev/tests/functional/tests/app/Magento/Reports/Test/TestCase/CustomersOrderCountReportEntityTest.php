<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Reports\Test\Page\Adminhtml\CustomerOrdersReport;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create customer
 * 2. Create orders for customer
 *
 * Steps:
 * 1. Login to backend
 * 2. Open Reports > Customer > Order Count
 * 3. Fill data from dataset
 * 4. Click button Refresh
 * 5. Perform all assertions
 *
 * @group Reports
 * @ZephyrId MAGETWO-28521
 */
class CustomersOrderCountReportEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const STABLE = 'no';
    /* end tags */

    /**
     * Order count report page.
     *
     * @var CustomerOrdersReport
     */
    protected $customerOrdersReport;

    /**
     * Inject page.
     *
     * @param CustomerOrdersReport $customerOrdersReport
     * @return void
     */
    public function __inject(CustomerOrdersReport $customerOrdersReport)
    {
        $this->customerOrdersReport = $customerOrdersReport;
    }

    /**
     * Order count report view.
     *
     * @param Customer $customer
     * @param string $orders
     * @param array $report
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function test(Customer $customer, $orders, array $report, FixtureFactory $fixtureFactory)
    {
        // Precondition
        $customer->persist();
        $orders = explode(',', $orders);
        foreach ($orders as $order) {
            $order = $fixtureFactory->createByCode(
                'orderInjectable',
                ['dataset' => $order, 'data' => ['customer_id' => ['customer' => $customer]]]
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
