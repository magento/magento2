<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Config\Test\Fixture\ConfigData;
use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Customer\Test\Page\CustomerAddressEdit;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Preconditions:
 * 1. Enable VAT functionality.
 * 2. Create customer.
 *
 * Steps:
 * 1. Go to frontend.
 * 2. Login with customer account.
 * 3. Go to My Account > Address Book.
 * 4. Update Default Billing Address with specified VAT number.
 * 5. Save Customer Address.
 * 6. Perform assertions.
 *
 * @group VAT_ID_(CS)
 * @ZephyrId MAGETWO-12447
 */
class ApplyVatIdTest extends AbstractApplyVatIdTest
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'CS';
    const TEST_TYPE = '3rd_party_test';
    /* end tags */

    /**
     * Customer account page.
     *
     * @var CustomerAccountIndex
     */
    protected $customerAccountIndex;

    /**
     * Customer account edit address page.
     *
     * @var CustomerAddressEdit
     */
    protected $customerAddressEdit;

    /**
     * Inject pages.
     *
     * @param CustomerAccountIndex $customerAccountIndex
     * @param CustomerAddressEdit $customerAddressEdit
     * @return void
     */
    public function __inject(CustomerAccountIndex $customerAccountIndex, CustomerAddressEdit $customerAddressEdit)
    {
        $this->customerAccountIndex = $customerAccountIndex;
        $this->customerAddressEdit = $customerAddressEdit;
    }

    /**
     * Enable Automatic Assignment of Customers to Appropriate VAT Group.
     *
     * @param Customer $customer
     * @param Address $vatId
     * @param ConfigData $vatConfig
     * @param string $configData
     * @param string $customerGroup
     * @return array
     */
    public function test(
        Customer $customer,
        Address $vatId,
        ConfigData $vatConfig,
        $configData,
        $customerGroup
    ) {
        // Preconditions
        $this->configData = $configData;
        $this->customer = $customer;
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData]
        )->run();
        $this->customer->persist();
        $this->prepareVatConfig($vatConfig, $customerGroup);

        // Steps
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $this->customer]
        )->run();
        $this->customerAccountIndex->getDashboardAddress()->editBillingAddress();
        $this->customerAddressEdit->getEditForm()->fill($vatId);
        $this->customerAddressEdit->getEditForm()->saveAddress();

        return ['customer' => $this->customer];
    }
}
