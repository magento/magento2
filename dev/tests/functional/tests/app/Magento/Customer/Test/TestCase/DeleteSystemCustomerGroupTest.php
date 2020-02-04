<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Login to backend as admin user.
 * 2. Navigate to Customers > Customer Groups.
 * 3. Select system Customer Group specified in data set from grid.
 * 4. Perform all assertions.
 *
 * @group Customer_Groups
 * @ZephyrId MAGETWO-53576
 */
class DeleteSystemCustomerGroupTest extends Injectable
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
     * Inject pages.
     *
     * @param CustomerGroupIndex $customerGroupIndex
     * @return void
     */
    public function __inject(CustomerGroupIndex $customerGroupIndex)
    {
        $this->customerGroupIndex = $customerGroupIndex;
    }

    /**
     * Check Customer Group cannot be deleted.
     *
     * @param CustomerGroup $customerGroup
     * @return void
     */
    public function test(CustomerGroup $customerGroup)
    {
        $filter = ['code' => $customerGroup->getCustomerGroupCode()];

        // Steps
        $this->customerGroupIndex->open();
        $this->customerGroupIndex->getCustomerGroupGrid()->searchAndOpen($filter);
    }
}
