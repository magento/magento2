<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupNew;
use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Preconditions:
 *  1. Customer Group is created.
 *  2. Customer is created and assigned to this group.
 * Steps:
 *  1. Log in to backend as admin user.
 *  2. Navigate to Customers > Customer Groups.
 *  3. Click on Customer Group from grid.
 *  4. Click on Delete "Customer Group".
 *  5. Confirm in pop-up.
 *  6. Perform all assertions.
 *
 * @group Customer_Groups
 * @ZephyrId MAGETWO-25243
 */
class DeleteCustomerGroupEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
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
     * @param CustomerGroup $customerGroup
     * @param Customer $customer [optional]
     * @return array
     */
    public function test(CustomerGroup $customerGroup, Customer $customer = null)
    {
        // Precondition
        if ($customer !== null) {
            $customer->persist();
            $customerGroup = $customer->getDataFieldConfig('group_id')['source']->getCustomerGroup();
        } else {
            $customerGroup->persist();
        }

        // Steps
        $filter = ['code' => $customerGroup->getCustomerGroupCode()];
        $this->customerGroupIndex->open();
        $this->customerGroupIndex->getCustomerGroupGrid()->searchAndOpen($filter);
        $this->customerGroupNew->getPageMainActions()->delete();
        $this->customerGroupNew->getModalBlock()->acceptAlert();

        return [
            'customer' => $customer,
            'customerGroup' => $customerGroup
        ];
    }
}
