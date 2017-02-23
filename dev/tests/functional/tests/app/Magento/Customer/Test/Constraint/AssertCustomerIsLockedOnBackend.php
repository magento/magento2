<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerIsLocked
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
     * @param CustomerIndex $customerIndex
     * @param Customer $customer
     * @return void
     */
    public function processAssert(
        CustomerIndexEdit $customerIndexEdit,
        CustomerIndex $customerIndex,
        Customer $customer
    ) {
        $customerIndex->open();
        $customerIndex->getCustomerGridBlock()->searchAndOpen(['email' => $customer->getEmail()]);
        \PHPUnit_Framework_Assert::assertEquals(
            self::CUSTOMER_LOCKED_ACCOUNT,
            $customerIndexEdit->getCustomerForm()->getPersonalInformation('Account Lock'),
            'Incorrect customer account status.'
        );
    }

    /**
     * Assert that displayed error message is correct.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer account status is correct.';
    }
}
