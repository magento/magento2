<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Customer\Test\Fixture\AddressInjectable;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexNew;
use Mtf\TestCase\Injectable;

/**
 * Test Coverage for CreateCustomerBackendEntityTest
 *
 * General Flow:
 * 1. Log in as default admin user.
 * 2. Go to Customers > All Customers
 * 3. Press "Add New Customer" button
 * 4. Fill form
 * 5. Click "Save Customer" button
 * 6. Perform all assertions
 *
 * @ticketId MAGETWO-23424
 */
class CreateCustomerBackendEntityTest extends Injectable
{
    /**
     * @var CustomerInjectable
     */
    protected $customer;

    /**
     * @var CustomerIndex
     */
    protected $pageCustomerIndex;

    /**
     * @var CustomerIndexNew
     */
    protected $pageCustomerIndexNew;

    /**
     * @param CustomerIndex $pageCustomerIndex
     * @param CustomerIndexNew $pageCustomerIndexNew
     */
    public function __inject(
        CustomerIndex $pageCustomerIndex,
        CustomerIndexNew $pageCustomerIndexNew
    ) {
        $this->pageCustomerIndex = $pageCustomerIndex;
        $this->pageCustomerIndexNew = $pageCustomerIndexNew;
    }

    /**
     * @param CustomerInjectable $customer
     * @param AddressInjectable $address
     */
    public function testCreateCustomerBackendEntity(CustomerInjectable $customer, AddressInjectable $address)
    {
        // Prepare data
        $address = $address->hasData() ? $address : null;

        // Steps
        $this->pageCustomerIndex->open();
        $this->pageCustomerIndex->getPageActionsBlock()->addNew();
        $this->pageCustomerIndexNew->getCustomerForm()->fillCustomer($customer, $address);
        $this->pageCustomerIndexNew->getPageActionsBlock()->save();
    }
}
