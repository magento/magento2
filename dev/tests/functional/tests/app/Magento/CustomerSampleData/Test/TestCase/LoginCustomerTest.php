<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerSampleData\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Page\CustomerAccountIndex;

/**
 * @group Sample_Data_(MX)
 * @ZephyrId MAGETWO-33559
 */
class LoginCustomerTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Create Customer account on Storefront.
     *
     * @param Customer $customer
     * @param CustomerAccountIndex $customerAccountIndex
     */
    public function test(Customer $customer, CustomerAccountIndex $customerAccountIndex)
    {
        // Steps
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('Account Information');
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('Address Book');
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('My Orders');
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('My Wish List');
    }
}
