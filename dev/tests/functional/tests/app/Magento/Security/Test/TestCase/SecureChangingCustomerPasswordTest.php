<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountEdit;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1.  Customer is created
 *
 * Steps:
 * 1. Login to fronted as customer from preconditions
 * 2. Navigate to Account Information tab
 * 3. Check "Change Password" checkbox
 * 4. Fill form according to data set and save
 * 5. Perform all assertions
 *
 * @group Security
 * @ZephyrId MAGETWO-49043
 */
class SecureChangingCustomerPasswordTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * CustomerAccountEdit page.
     *
     * @var CustomerAccountEdit
     */
    protected $customerAccountEdit;

    /**
     * Preparing page for test.
     *
     * @param CustomerAccountEdit $customerAccountEdit
     * @return void
     */
    public function __inject(
        CustomerAccountEdit $customerAccountEdit
    ) {
        $this->customerAccountEdit = $customerAccountEdit;
    }

    /**
     * Change customer password in Account Information tab.
     *
     * @param Customer $initialCustomer
     * @param Customer $customer
     * @param boolean $check
     * @return void
     */
    public function test(Customer $initialCustomer, Customer $customer, $check)
    {
        // Preconditions
        $initialCustomer->persist();

        // Steps
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $initialCustomer]
        )->run();

        $this->customerAccountEdit->getAccountMenuBlock()->openMenuItem('Account Information');
        if ($check) {
            $this->customerAccountEdit->getAccountInfoForm()->SetChangePassword(true);
            $this->customerAccountEdit->getAccountInfoForm()->fill($customer);
            $this->customerAccountEdit->getAccountInfoForm()->submit();
        }
    }

    /**
     * Logout customer from frontend account.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(\Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class)->run();
    }
}
