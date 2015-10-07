<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test creation for MassDeleteCustomerBackendEntityTest
 *
 * Test Flow:
 * Preconditions:
 * 1. Create X customers
 *
 * Steps:
 * 1. Open backend
 * 2. Go to  Customers - All Customers
 * 3. Select N customers from preconditions
 * 4. Select in dropdown "Delete"
 * 5. Click Submit button
 * 6. Perform all assertions according to dataset
 *
 * @group Customers_(CS)
 * @ZephyrId MAGETWO-26848
 */
class MassDeleteCustomerBackendEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Customer Index page
     *
     * @var CustomerIndex
     */
    protected $customerIndexPage;

    /**
     * Customer Index Edit page
     *
     * @var CustomerIndexEdit
     */
    protected $customerIndexEditPage;

    /**
     * Factory for fixture
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Preparing pages for test
     *
     * @param FixtureFactory $fixtureFactory
     * @param CustomerIndex $customerIndexPage
     * @param CustomerIndexEdit $customerIndexEditPage
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CustomerIndex $customerIndexPage,
        CustomerIndexEdit $customerIndexEditPage
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->customerIndexPage = $customerIndexPage;
        $this->customerIndexEditPage = $customerIndexEditPage;
    }

    /**
     * Runs Delete Customer Backend Entity test
     *
     * @param int $customersQty
     * @param int $customersQtyToDelete
     * @return array
     */
    public function test($customersQty, $customersQtyToDelete)
    {
        // Preconditions:
        $customers = $this->createCustomers($customersQty);
        $deleteCustomers = [];
        for ($i = 0; $i < $customersQtyToDelete; $i++) {
            $deleteCustomers[] = ['email' => $customers[$i]->getEmail()];
        }
        // Steps:
        $this->customerIndexPage->open();
        $this->customerIndexPage->getCustomerGridBlock()->massaction($deleteCustomers, 'Delete', true);

        return ['customers' => $customers];
    }

    /**
     * Create Customers
     *
     * @param int $customersQty
     * @return Customer[]
     */
    protected function createCustomers($customersQty)
    {
        $customers = [];
        for ($i = 0; $i < $customersQty; $i++) {
            $customer = $this->fixtureFactory->createByCode('customer', ['dataset' => 'default']);
            $customer->persist();
            $customers[] = $customer;
        }

        return $customers;
    }
}
