<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Precondition:
 * 1. Customer is created.
 *
 * Steps:
 * 1. Open login page.
 * 2. Fill email.
 * 3. Fill wrong password.
 * 4. Click login.
 * 5. Check error message.
 *
 * @group Customer
 * @ZephyrId MAGETWO-16883
 */
class LoginOnFrontendFailTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Login on frontend.
     *
     * @param Customer $customer
     * @param CustomerAccountLogin $loginPage
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function test(Customer $customer, CustomerAccountLogin $loginPage, FixtureFactory $fixtureFactory)
    {
        // Precondition
        $customer->persist();
        $customerData = $customer->getData();
        $customerData['password'] = 'fail';
        $customerData['group_id'] = ['dataset' => 'default'];
        $failCustomer = $fixtureFactory->createByCode('customer', ['data' => $customerData]);

        // Steps
        $loginPage->open();
        $loginPage->getLoginBlock()->fill($failCustomer);
        $loginPage->getLoginBlock()->submit();
    }
}
