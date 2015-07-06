<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for UpdateCustomerBackendEntity
 *
 * General Flow:
 * 1. Login to backend as admin
 * 2. Navigate to CUSTOMERS->All Customers
 * 3. Open from grid test customer
 * 4. Edit some values, if addresses fields are not presented click 'Add New Address' button
 * 5. Click 'Save' button
 * 6. Perform all assertions
 *
 * @ZephyrId MAGETWO-23881
 */
class UpdateCustomerBackendEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    const STABLE = 'no';
    /* end tags */

    /**
     * @var CustomerIndex
     */
    protected $customerIndexPage;

    /**
     * @var CustomerIndexEdit
     */
    protected $customerIndexEditPage;

    /**
     * @param CustomerIndex $customerIndexPage
     * @param CustomerIndexEdit $customerIndexEditPage
     */
    public function __inject(
        CustomerIndex $customerIndexPage,
        CustomerIndexEdit $customerIndexEditPage
    ) {
        $this->customerIndexPage = $customerIndexPage;
        $this->customerIndexEditPage = $customerIndexEditPage;
    }

    /**
     * @param Customer $initialCustomer
     * @param Customer $customer
     * @param Address $address
     */
    public function testUpdateCustomerBackendEntity(
        Customer $initialCustomer,
        Customer $customer,
        Address $address
    ) {
        // Prepare data
        $address = $address->hasData() ? $address : null;

        // Preconditions:
        $initialCustomer->persist();

        // Steps
        $filter = ['email' => $initialCustomer->getEmail()];
        $this->customerIndexPage->open();
        $this->customerIndexPage->getCustomerGridBlock()->searchAndOpen($filter);
        $this->customerIndexEditPage->getCustomerForm()->updateCustomer($customer, $address);
        $this->customerIndexEditPage->getPageActionsBlock()->save();
    }
}
