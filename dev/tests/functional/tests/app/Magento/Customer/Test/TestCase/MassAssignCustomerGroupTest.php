<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Customer\Test\Fixture\CustomerGroupInjectable;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Mtf\TestCase\Injectable;

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
     * @param CustomerInjectable $customer
     * @return array
     */
    public function __prepare(CustomerInjectable $customer)
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
     * @param CustomerInjectable $customer
     * @param CustomerGroupInjectable $customerGroup
     * @return void
     */
    public function test(CustomerInjectable $customer, CustomerGroupInjectable $customerGroup)
    {
        // Steps
        $customerGroup->persist();
        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->massaction(
            [['email' => $customer->getEmail()]],
            [$this->customersGridActions => $customerGroup->getCustomerGroupCode()]
        );
    }
}
