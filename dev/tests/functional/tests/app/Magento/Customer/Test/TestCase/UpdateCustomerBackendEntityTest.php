<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Mtf\TestCase\Injectable;

/**
 * Precondition:
 * 1. Create customer.
 *
 * Steps:
 * 1. Login to backend as admin.
 * 2. Navigate to CUSTOMERS->All Customers.
 * 3. Open from grid test customer.
 * 4. Edit some values, if addresses fields are not presented click 'Add New Address' button.
 * 5. Click 'Save' button.
 * 6. Perform all assertions.
 *
 * @ZephyrId MAGETWO-23881
 */
class UpdateCustomerBackendEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Customer grid page.
     *
     * @var CustomerIndex
     */
    protected $customerIndexPage;

    /**
     * Customer edit page.
     *
     * @var CustomerIndexEdit
     */
    protected $customerIndexEditPage;

    /**
     * Inject pages.
     *
     * @param CustomerIndex $customerIndexPage
     * @param CustomerIndexEdit $customerIndexEditPage
     * @return void
     */
    public function __inject(
        CustomerIndex $customerIndexPage,
        CustomerIndexEdit $customerIndexEditPage
    ) {
        $this->customerIndexPage = $customerIndexPage;
        $this->customerIndexEditPage = $customerIndexEditPage;
    }

    /**
     * Run update customer test.
     *
     * @param Customer $initialCustomer
     * @param Customer $customer
     * @param Address $address [optional]
     * @return void
     */
    public function testUpdateCustomerBackendEntity(
        Customer $initialCustomer,
        Customer $customer,
        Address $address = null
    ) {
        // Precondition
        $initialCustomer->persist();

        // Steps
        $filter = ['email' => $initialCustomer->getEmail()];
        $this->customerIndexPage->open();
        $this->customerIndexPage->getCustomerGridBlock()->searchAndOpen($filter);
        $this->customerIndexEditPage->getCustomerForm()->updateCustomer($customer, $address);
        $this->customerIndexEditPage->getPageActionsBlock()->save();
    }
}
