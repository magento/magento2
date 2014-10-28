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

use Mtf\TestCase\Injectable;
use Mtf\Fixture\FixtureFactory;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Reports\Test\Page\Adminhtml\CustomerTotalsReport;

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
