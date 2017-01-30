<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test creation for MassAssignCustomerGroup
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create customer
 * 2. Create customer group
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Customers> All Customers
 * 3. Find and select(using checkbox) created customer
 * 4. Select "Assign a Customer Group" from action drop-down
 * 5. Select created customer group
 * 6. Click "Submit" button
 * 7. Perform all assertions
 *
 * @group Customer_Groups_(CS), Customers_(CS)
 * @ZephyrId MAGETWO-27892
 */
class MassAssignCustomerGroupTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Customer index page
     *
     * @var CustomerIndex
     */
    protected $customerIndex;

    /**
     * Customers grid actions
     *
     * @var string
     */
    protected $customersGridActions = 'Assign a Customer Group';

    /**
     * Prepare data
     *
     * @param Customer $customer
     * @return array
     */
    public function __prepare(Customer $customer)
    {
        $customer->persist();

        return ['customer' => $customer];
    }

    /**
     * Injection data
     *
     * @param CustomerIndex $customerIndex
     * @return void
     */
    public function __inject(CustomerIndex $customerIndex)
    {
        $this->customerIndex = $customerIndex;
    }

    /**
     * Mass assign customer group
     *
     * @param Customer $customer
     * @param CustomerGroup $customerGroup
     * @return void
     */
    public function test(Customer $customer, CustomerGroup $customerGroup)
    {
        // Steps
        $customerGroup->persist();
        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->massaction(
            [['email' => $customer->getEmail()]],
            [$this->customersGridActions => $customerGroup->getCustomerGroupCode()],
            true
        );
    }
}
