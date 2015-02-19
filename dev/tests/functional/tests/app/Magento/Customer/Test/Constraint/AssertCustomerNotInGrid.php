<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerNotInGrid
 * Check that customer is not in customer's grid
 */
class AssertCustomerNotInGrid extends AbstractConstraint
{
    /**
     * Asserts that customer is not in customer's grid
     *
     * @param Customer $customer
     * @param CustomerIndex $customerIndexPage
     * @return void
     */
    public function processAssert(
        Customer $customer,
        CustomerIndex $customerIndexPage
    ) {
        $customerIndexPage->open();
        \PHPUnit_Framework_Assert::assertFalse(
            $customerIndexPage->getCustomerGridBlock()->isRowVisible(['email' => $customer->getEmail()]),
            'Customer with email ' . $customer->getEmail() . 'is present in Customer grid.'
        );
    }

    /**
     * Success message if Customer not in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer is absent in Customer grid.';
    }
}
