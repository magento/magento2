<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test creation for MassAssignCustomerGroup
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create customers
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
 * @group Customer_Groups, Customers
 * @ZephyrId MAGETWO-27892, MAGETWO-19456
 */
class MassAssignCustomerGroupTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'extended_acceptance_test';
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
     * @param CustomerGroup $customerGroup
     * @param FixtureFactory $fixtureFactory
     * @param array $customers
     * @return array
     */
    public function test(CustomerGroup $customerGroup, FixtureFactory $fixtureFactory, array $customers)
    {
        // Preconditions
        if (!$customerGroup->hasData('customer_group_id')) {
            $customerGroup->persist();
        }

        $customerEmails = [];
        foreach ($customers as &$customer) {
            $customer = $fixtureFactory->createByCode('customer', ['dataset' => $customer]);
            $customer->persist();
            $customerEmails[] = ['email' => $customer->getEmail()];
        }

        // Steps
        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->massaction(
            $customerEmails,
            [$this->customersGridActions => $customerGroup->getCustomerGroupCode()],
            true
        );
        return ['customers' => $customers];
    }
}
