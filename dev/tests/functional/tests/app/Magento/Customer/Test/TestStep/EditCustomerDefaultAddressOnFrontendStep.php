<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestStep;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Customer\Test\Page\CustomerAddressEdit;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Edit customer address in customer account.
 */
class EditCustomerDefaultAddressOnFrontendStep implements TestStepInterface
{
    const   ADDRESS_TYPE_BILLING = 'billing',
            ADDRESS_TYPE_SHIPPING = 'shipping';

    /**
     * Address fixture.
     *
     * @var Address
     */
    private $address;

    /**
     * Address type.
     *
     * @var string
     */
    private $addressType = self::ADDRESS_TYPE_BILLING;

    /**
     * Customer account dashboard page.
     *
     * @var CustomerAccountIndex
     */
    private $customerAccountIndex;

    /**
     * Customer address edit page.
     *
     * @var CustomerAddressEdit
     */
    private $customerAddressEdit;

    /**
     * @param CustomerAccountIndex $customerAccountIndex
     * @param CustomerAddressEdit $customerAddressEdit
     * @param Address $address
     * @param string $addressType
     */
    public function __construct(
        CustomerAccountIndex $customerAccountIndex,
        CustomerAddressEdit $customerAddressEdit,
        Address $address,
        $addressType = self::ADDRESS_TYPE_BILLING
    ) {
        $this->address = $address;
        $this->addressType = $addressType;
        $this->customerAccountIndex = $customerAccountIndex;
        $this->customerAddressEdit = $customerAddressEdit;
    }

    /**
     * Run step flow.
     *
     * @return void
     */
    public function run()
    {
        $this->customerAccountIndex->open();

        $editAddress = '';
        switch ($this->addressType) {
            case self::ADDRESS_TYPE_BILLING:
                $editAddress = 'editBillingAddress';
                break;
            case self::ADDRESS_TYPE_SHIPPING:
                $editAddress = 'editShippingAddress';
                break;
        }

        $this->customerAccountIndex->getDashboardAddress()->$editAddress();
        $this->customerAddressEdit->getEditForm()->editCustomerAddress($this->address);
    }
}
