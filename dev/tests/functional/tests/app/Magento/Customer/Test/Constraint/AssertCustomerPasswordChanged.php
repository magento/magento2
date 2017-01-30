<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Check that login again to frontend with new password was success.
 */
class AssertCustomerPasswordChanged extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that login again to frontend with new password was success.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CustomerAccountIndex $customerAccountIndex
     * @param Customer $initialCustomer
     * @param Customer $customer
     * @return void
     */
    public function processAssert(
        FixtureFactory $fixtureFactory,
        CustomerAccountIndex $customerAccountIndex,
        Customer $initialCustomer,
        Customer $customer
    ) {
        $customer = $fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'default',
                'data' => [
                    'email' => $initialCustomer->getEmail(),
                    'password' => $customer->getPassword(),
                    'password_confirmation' => $customer->getPassword(),
                ],
            ]
        );

        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();

        \PHPUnit_Framework_Assert::assertTrue(
            $customerAccountIndex->getAccountMenuBlock()->isVisible(),
            'Customer Account Dashboard is not visible.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer password was changed.';
    }
}
