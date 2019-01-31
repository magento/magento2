<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Block\Address\Renderer;
use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Page\CustomerAccountAddress;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert customers address on Address Book in Customer Account.
 */
class AssertCustomerDefaultAddressFrontendAddressBook extends AbstractConstraint
{
    /**
     * Asserts that Default Billing Address and Default Shipping Address equal to data from fixture.
     *
     * @param CustomerAccountIndex $customerAccountIndex
     * @param CustomerAccountAddress $customerAddress
     * @param Address|null $shippingAddress
     * @param Address|null $billingAddress
     * @return void
     */
    public function processAssert(
        CustomerAccountIndex $customerAccountIndex,
        CustomerAccountAddress $customerAddress,
        Address $shippingAddress,
        Address $billingAddress = null
    ) {
        $customerAccountIndex->open();
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('Address Book');

        $shippingAddressRendered = $this->createAddressRenderer($shippingAddress)->render();
        $defaultShippingAddress = $customerAddress->getDefaultAddressBlock()->getDefaultShippingAddress();
        $validated = strpos($defaultShippingAddress, trim($shippingAddressRendered)) !== false;
        if (null !== $billingAddress) {
            $billingAddressRendered = $customerAddress->getDefaultAddressBlock()->getDefaultBillingAddress();
            $validated =
                $validated && ($billingAddressRendered == $this->createAddressRenderer($billingAddress)->render());
        }

        \PHPUnit\Framework\Assert::assertTrue(
            $validated,
            'Customer default address on address book tab is not matching the fixture.'
        );
    }

    /**
     * String representation of success assert.
     *
     * @return string
     */
    public function toString()
    {
        return 'Default billing and shipping address form is correct.';
    }

    /**
     * Instantiate Renderer object.
     *
     * @param Address $address
     * @return Renderer
     */
    private function createAddressRenderer(Address $address)
    {
        return $this->objectManager->create(
            Renderer::class,
            ['address' => $address, 'type' => 'html']
        );
    }
}
