<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\TestCase;

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
 * 1. Create customer.
 * 2. Configure maximum login failures to lockout customer.
 *
 * Steps:
 * 1. Login to fronted as customer from preconditions.
 * 2. Navigate to My Account page.
 * 3. Click "Change Password" link near "Contact Information".
 * 4. Fill form according to data set and save (current password is incorrect).
 * 5. Perform action for specified number of times.
 * 6. "The password doesn't match this account. Verify the password and try again." appears after each
 *    change password attempt.
 * 7. Perform all assertions.
 *
 * @ZephyrId MAGETWO-50559
 */
class LockCustomerOnEditPageTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S1';
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
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

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
     * Run Lock customer on edit page test.
     *
     * @param Customer $initialCustomer
     * @param Customer $customer
     * @param int $attempts
     * @param string $configData
     * @return void
     */
    public function test(
        Customer $initialCustomer,
        Customer $customer,
        $attempts,
        $configData = null
    ) {
        $this->configData = $configData;
        // Preconditions
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $initialCustomer->persist();

        // Steps
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $initialCustomer]
        )->run();

        $this->cmsIndex->getLinksBlock()->openLink('My Account');
        $this->customerAccountIndex->getInfoBlock()->openChangePassword();
        for ($i = 0; $i < $attempts; $i++) {
            if ($i > 0) {
                $this->customerAccountIndex->getInfoBlock()->checkChangePassword();
            }
            $this->customerAccountEdit->getAccountInfoForm()->fill($customer);
            $this->customerAccountEdit->getAccountInfoForm()->submit();
        }
    }

    /**
     * Clean data after running test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
