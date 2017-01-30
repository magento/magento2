<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test creation for DeleteCustomerBackendEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Create customer on the backend
 *
 * Steps:
 * 1. Open backend
 * 2. Go to  Customers - All Customers
 * 3. Search and open created customer according to dataset
 * 4. Fill in data according to dataset
 * 5. Perform all assertions according to dataset
 *
 * @group Customers_(CS)
 * @ZephyrId MAGETWO-24764
 */
class DeleteCustomerBackendEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
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
     * Preparing pages for test
     *
     * @param CustomerIndex $customerIndexPage
     * @param CustomerIndexEdit $customerIndexEditPage
     * @return void
     */
    public function __inject(CustomerIndex $customerIndexPage, CustomerIndexEdit $customerIndexEditPage)
    {
        $this->customerIndexPage = $customerIndexPage;
        $this->customerIndexEditPage = $customerIndexEditPage;
    }

    /**
     * Runs Delete Customer Backend Entity test
     *
     * @param Customer $customer
     * @return void
     */
    public function testDeleteCustomerBackendEntity(Customer $customer)
    {
        // Preconditions:
        $customer->persist();

        // Steps:
        $filter = ['email' => $customer->getEmail()];
        $this->customerIndexPage->open();
        $this->customerIndexPage->getCustomerGridBlock()->searchAndOpen($filter);
        $this->customerIndexEditPage->getPageActionsBlock()->delete();
        $this->customerIndexEditPage->getModalBlock()->acceptAlert();
    }
}
