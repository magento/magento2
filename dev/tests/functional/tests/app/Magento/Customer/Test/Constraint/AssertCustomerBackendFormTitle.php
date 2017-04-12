<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Assert that edit page of customer account contains correct title.
 */
class AssertCustomerBackendFormTitle extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'middle';
    /* end tags */

    /**
     * Assert that edit page of customer account contains correct title.
     *
     * @param CustomerAccountIndex $pageCustomerIndex
     * @param Customer $customer
     * @return void
     */
    public function processAssert(CustomerAccountIndex $pageCustomerIndex, Customer $customer)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $customer->getFirstname() . ' ' . $customer->getLastname(),
            $pageCustomerIndex->getTitleBlock()->getTitle(),
            'Wrong page title is displayed.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer backend edit form title is correct.';
    }
}
