<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Reports\Test\Page\Adminhtml\CustomerAccounts;
use Mtf\TestCase\Injectable;

/**
 * Test Flow:
 * Preconditions:
 * 1. Delete all existing customers.
 * 2. Create customer.
 *
 * Steps:
 * 1. Open Backend.
 * 2. Go to Reports> Customers> New.
 * 3. Select time range and report period.
 * 4. Click "Refresh button".
 * 5. Perform all assertions.
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-27742
 */
class NewAccountsReportEntityTest extends Injectable
{
    /**
     * Customer Accounts pages.
     *
     * @var CustomerAccounts
     */
    protected $customerAccounts;

    /**
     * Customer index pages.
     *
     * @var CustomerIndex
     */
    protected $customerIndexPage;

    /**
     * Inject pages.
     *
     * @param CustomerIndex $customerIndexPage
     * @param CustomerAccounts $customerAccounts
     * @return void
     */
    public function __inject(CustomerIndex $customerIndexPage, CustomerAccounts $customerAccounts)
    {
        $this->customerAccounts = $customerAccounts;
        $this->customerIndexPage = $customerIndexPage;
    }

    /**
     * New Accounts Report.
     *
     * @param CustomerInjectable $customer
     * @param array $customersReport
     * @return void
     */
    public function test(CustomerInjectable $customer, array $customersReport)
    {
        $this->markTestIncomplete('MAGETWO-26663');
        // Preconditions
        $this->customerIndexPage->open();
        $this->customerIndexPage->getCustomerGridBlock()->massaction([], 'Delete', true, 'Select All');
        $customer->persist();

        // Steps
        $this->customerAccounts->open();
        $this->customerAccounts->getGridBlock()->searchAccounts($customersReport);
    }
}
