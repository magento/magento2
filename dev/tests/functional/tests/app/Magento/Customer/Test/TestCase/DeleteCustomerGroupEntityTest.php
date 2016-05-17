<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupNew;
use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Test Creation for DeleteCustomerGroupEntity.
 *
 * Test Flow:
 * Preconditions:
 *  1. Customer Group is created.
 *  2. Customer is created and assigned to this group.
 * Steps:
 *  1. Log in to backend as admin user.
 *  2. Navigate to Stores > Other Settings > Customer Groups.
 *  3. Click on Customer Group from grid.
 *  4. Click on Delete "Customer Group".
 *  5. Confirm in pop-up.
 *  6. Perform all assertions.
 *
 * @group Customer_Groups_(CS)
 * @ZephyrId MAGETWO-25243
 */
class DeleteCustomerGroupEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Page CustomerGroupIndex.
     *
     * @var CustomerGroupIndex
     */
    protected $customerGroupIndex;

    /**
     * Page CustomerGroupNew.
     *
     * @var CustomerGroupNew
     */
    protected $customerGroupNew;

    /**
     * Injection data.
     *
     * @param CustomerGroupIndex $customerGroupIndex
     * @param CustomerGroupNew $customerGroupNew
     * @return void
     */
    public function __inject(
        CustomerGroupIndex $customerGroupIndex,
        CustomerGroupNew $customerGroupNew
    ) {
        $this->customerGroupIndex = $customerGroupIndex;
        $this->customerGroupNew = $customerGroupNew;
    }

    /**
     * Delete Customer Group.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CustomerGroup $customerGroup
     * @return array
     */
    public function test(
        FixtureFactory $fixtureFactory,
        CustomerGroup $customerGroup
    ) {
        // Precondition
        $customerGroup->persist();

        /** @var Customer $customer */
        $customer = $fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'default',
                'data' => ['group_id' => ['customerGroup' => $customerGroup]]
            ]
        );
        $customer->persist();

        // Steps
        $filter = ['code' => $customerGroup->getCustomerGroupCode()];
        $this->customerGroupIndex->open();
        $this->customerGroupIndex->getCustomerGroupGrid()->searchAndOpen($filter);
        $this->customerGroupNew->getPageMainActions()->delete();
        $this->customerGroupNew->getModalBlock()->acceptAlert();

        return ['customer' => $customer];
    }
}
