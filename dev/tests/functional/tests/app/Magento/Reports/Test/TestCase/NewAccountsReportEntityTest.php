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
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Reports\Test\Page\Adminhtml\CustomerAccounts;

/**
 * Test Creation for NewAccountsReportEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Delete all existing customers
 * 2. Create customer
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Reports> Customers> New
 * 3. Select time range and report period
 * 4. Click "Refresh button"
 * 5. Perform all assertions
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-27742
 */
class NewAccountsReportEntityTest extends Injectable
{
    /**
     * Customer Accounts pages
     *
     * @var CustomerAccounts
     */
    protected $customerAccounts;

    /**
     * Customer index pages
     *
     * @var CustomerIndex
     */
    protected $customerIndexPage;

    /**
     * Inject pages
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
     * New Accounts Report
     *
     * @param CustomerInjectable $customer
     * @param array $customersReport
     * @return void
     */
    public function test(CustomerInjectable $customer, array $customersReport)
    {
        // Preconditions
        $this->customerIndexPage->open();
        $this->customerIndexPage->getCustomerGridBlock()->massaction([], 'Delete', true, 'Select All');
        $customer->persist();

        // Steps
        $this->customerAccounts->open();
        $this->customerAccounts->getGridBlock()->searchAccounts($customersReport);
    }
}
