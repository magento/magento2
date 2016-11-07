<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Check that login again to frontend with new email was successful.
 */
class AssertCustomerEmailChanged extends AbstractConstraint
{
    /**
     * Assert that login again to frontend with new email was successful.
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
                'data' => [
                    'email' => $customer->getEmail(),
                    'password' => $initialCustomer->getPassword()
                ],
            ]
        );

        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
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
        return 'Customer email was changed.';
    }
}
