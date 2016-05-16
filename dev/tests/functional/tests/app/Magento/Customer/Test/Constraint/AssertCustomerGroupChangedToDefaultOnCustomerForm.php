<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerGroupChangedToDefaultOnCustomerForm.
 */
class AssertCustomerGroupChangedToDefaultOnCustomerForm extends AbstractConstraint
{
    /**
     * Assert that customer group is General on account information page.
     *
     * @param Customer $customer
     * @param CustomerGroup $customerGroup
     * @param CustomerIndexNew $customerIndexNew
     * @param CustomerIndex $customerIndex
     * @return void
     */
    public function processAssert(
        Customer $customer,
        CustomerGroup $customerGroup,
        CustomerIndexNew $customerIndexNew,
        CustomerIndex $customerIndex
    ) {
        $filter = ['email' => $customer->getEmail()];
        $customerIndex->open();
        $customerIndex->getCustomerGridBlock()->searchAndOpen($filter);
        $customerFormData = $customerIndexNew->getCustomerForm()->getData();
        \PHPUnit_Framework_Assert::assertTrue(
            $customerFormData['group_id'] == "General",
            "Customer group {$customerGroup->getCustomerGroupCode()} not set to General after group was deleted."
        );
    }

    /**
     * Success assert of customer group set to default on account information page.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group is set to default on account information page.';
    }
}
