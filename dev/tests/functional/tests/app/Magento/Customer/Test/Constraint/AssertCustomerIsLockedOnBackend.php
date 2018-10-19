<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that customer account is locked on backend.
 */
class AssertCustomerIsLockedOnBackend extends AbstractConstraint
{
    /**
     * Customer account status.
     */
    const CUSTOMER_LOCKED_ACCOUNT = 'Locked';

    /**
     * Assert customer account status on the backend.
     *
     * @param CustomerIndexEdit $customerIndexEdit
     * @param Customer $customer
     * @return void
     */
    public function processAssert(
        CustomerIndexEdit $customerIndexEdit,
        Customer $customer
    ) {
        $customerIndexEdit->open(['id' => $customer->getId()]);
        \PHPUnit\Framework\Assert::assertEquals(
            self::CUSTOMER_LOCKED_ACCOUNT,
            $customerIndexEdit->getCustomerForm()->getPersonalInformation('Account Lock'),
            'Incorrect customer account status.'
        );
    }

    /**
     * Assert that displayed customer account status is correct.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer account status is correct.';
    }
}
