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

/**
 * Test Creation for Update Customer Group Entity
 *
 * Test Flow:
 * Preconditions:
 * 1. Customer Group is created
 * Steps:
 * 1. Log in to backend as admin user
 * 2. Navigate to Stores > Other Settings > Customer Groups
 * 3. Click on Customer Group from grid
 * 4. Update data according to data set
 * 5. Click "Save Customer Group" button
 * 6. Perform all assertions
 *
 * @group Customer_Groups_(CS)
 * @ZephyrId MAGETWO-25536
 */
class UpdateCustomerGroupEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Page CustomerGroupIndex
     *
     * @var CustomerGroupIndex
     */
    protected $customerGroupIndex;

    /**
     * Page CustomerGroupNew
     *
     * @var CustomerGroupNew
     */
    protected $customerGroupNew;

    /**
     * Injection data
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
     * Update Customer Group
     *
     * @param CustomerGroup $customerGroupOriginal
     * @param CustomerGroup $customerGroup
     * @return void
     */
    public function test(
        CustomerGroup $customerGroupOriginal,
        CustomerGroup $customerGroup
    ) {
        // Precondition
        $customerGroupOriginal->persist();
        $filter = ['code' => $customerGroupOriginal->getCustomerGroupCode()];

        // Steps
        $this->customerGroupIndex->open();
        $this->customerGroupIndex->getCustomerGroupGrid()->searchAndOpen($filter);
        $this->customerGroupNew->getPageMainForm()->fill($customerGroup);
        $this->customerGroupNew->getPageMainActions()->save();
    }
}
