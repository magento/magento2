<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountEdit;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Customer\Test\Page\CustomerAccountLogin;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Create customer
 *
 * Steps:
 * 1. Login to fronted as customer from preconditions
 * 2. Navigate to My Account page
 * 3. Click "Change Password" link near "Contact Information"
 * 4. Fill form according to data set and save
 * 5. Perform all assertions
 *
 * @group Customer_Account
 * @ZephyrId MAGETWO-29411
 */
class ChangeCustomerPasswordTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * CmsIndex page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * CustomerAccountLogin page.
     *
     * @var CustomerAccountLogin
     */
    protected $customerAccountLogin;

    /**
     * CustomerAccountIndex page.
     *
     * @var CustomerAccountIndex
     */
    protected $customerAccountIndex;

    /**
     * CustomerAccountEdit page.
     *
     * @var CustomerAccountEdit
     */
    protected $customerAccountEdit;

    /**
     * Preparing pages for test.
     *
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountLogin $customerAccountLogin
     * @param CustomerAccountIndex $customerAccountIndex
     * @param CustomerAccountEdit $customerAccountEdit
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        CustomerAccountLogin $customerAccountLogin,
        CustomerAccountIndex $customerAccountIndex,
        CustomerAccountEdit $customerAccountEdit
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->customerAccountLogin = $customerAccountLogin;
        $this->customerAccountIndex = $customerAccountIndex;
        $this->customerAccountEdit = $customerAccountEdit;
    }

    /**
     * Run Change customer password test.
     *
     * @param Customer $initialCustomer
     * @param Customer $customer
     * @return void
     */
    public function test(Customer $initialCustomer, Customer $customer)
    {
        // Preconditions
        $initialCustomer->persist();

        // Steps
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $initialCustomer]
        )->run();

        $this->cmsIndex->getLinksBlock()->openLink('My Account');
        $this->customerAccountIndex->getInfoBlock()->openChangePassword();
        $this->customerAccountEdit->getAccountInfoForm()->fill($customer);
        $this->customerAccountEdit->getAccountInfoForm()->submit();
    }
}
