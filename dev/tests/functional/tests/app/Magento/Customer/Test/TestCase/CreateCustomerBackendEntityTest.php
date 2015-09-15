<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexNew;

/**
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Customers > All Customers.
 * 3. Press "Add New Customer" button.
 * 4. Fill form.
 * 5. Click "Save Customer" button.
 * 6. Perform all assertions.
 *
 * @ZephyrId MAGETWO-23424
 */
class CreateCustomerBackendEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Customer fixture.
     *
     * @var Customer
     */
    protected $customer;

    /**
     * Customer index page.
     *
     * @var CustomerIndex
     */
    protected $pageCustomerIndex;

    /**
     * New customer page.
     *
     * @var CustomerIndexNew
     */
    protected $pageCustomerIndexNew;

    /**
     * Inject customer pages.
     *
     * @param CustomerIndex $pageCustomerIndex
     * @param CustomerIndexNew $pageCustomerIndexNew
     * @return void
     */
    public function __inject(
        CustomerIndex $pageCustomerIndex,
        CustomerIndexNew $pageCustomerIndexNew
    ) {
        $this->pageCustomerIndex = $pageCustomerIndex;
        $this->pageCustomerIndexNew = $pageCustomerIndexNew;
    }

    /**
     * Create customer on backend.
     *
     * @param Customer $customer
     * @param string $customerAction
     * @param Address $address
     * @return void
     */
    public function test(Customer $customer, $customerAction, Address $address = null)
    {
        // Steps
        $this->pageCustomerIndex->open();
        $this->pageCustomerIndex->getPageActionsBlock()->addNew();
        $this->pageCustomerIndexNew->getCustomerForm()->fillCustomer($customer, $address);
        $this->pageCustomerIndexNew->getPageActionsBlock()->$customerAction();
    }
}
