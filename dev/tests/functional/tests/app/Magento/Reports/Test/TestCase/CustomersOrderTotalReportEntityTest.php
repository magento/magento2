<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Reports\Test\Page\Adminhtml\CustomerTotalsReport;
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
 * 2. Open Reports > Customer > Order Total
 * 3. Fill data from dataset
 * 4. Click button Refresh
 * 5. Perform all assertions
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-28358
 */
class CustomersOrderTotalReportEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'MX';
    /* end tags */

    /**
     * Order total report page.
     *
     * @var CustomerTotalsReport
     */
    protected $customerTotalsReport;

    /**
     * Inject page.
     *
     * @param CustomerTotalsReport $customerTotalsReport
     * @return void
     */
    public function __inject(CustomerTotalsReport $customerTotalsReport)
    {
        $this->customerTotalsReport = $customerTotalsReport;
    }

    /**
     * Order total report view.
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
        $this->customerTotalsReport->open();
        $this->customerTotalsReport->getFilterBlock()->viewsReport($report);
        $this->customerTotalsReport->getFilterBlock()->refreshFilter();

        return['customer' => $customer];
    }
}
