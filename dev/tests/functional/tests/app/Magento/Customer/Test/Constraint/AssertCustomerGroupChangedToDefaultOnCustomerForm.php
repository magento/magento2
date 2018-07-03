<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that customer group is set to default on customer form.
 */
class AssertCustomerGroupChangedToDefaultOnCustomerForm extends AbstractConstraint
{
    /**
     * Assert that customer group is set to default on customer form.
     *
     * @param Customer $customer
     * @param CustomerGroup $defaultCustomerGroup
     * @param CustomerIndexNew $customerIndexNew
     * @param CustomerIndexNew $customerIndexEdit
     * @return void
     */
    public function processAssert(
        Customer $customer,
        CustomerGroup $defaultCustomerGroup,
        CustomerIndexNew $customerIndexNew,
        CustomerIndexNew $customerIndexEdit
    ) {
        $customerIndexEdit->open(['id' => $customer->getId()]);
        $customerFormData = $customerIndexNew->getCustomerForm()->getData($customer);
        \PHPUnit\Framework\Assert::assertTrue(
            $customerFormData['group_id'] == $defaultCustomerGroup->getCustomerGroupCode(),
            "Customer group not set to default after group was deleted."
        );
    }

    /**
     * Success assert of customer group set to default on customer form.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group is set to default on customer form.';
    }
}
