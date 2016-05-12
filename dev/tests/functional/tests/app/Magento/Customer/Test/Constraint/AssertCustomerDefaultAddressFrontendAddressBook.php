<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Customer\Test\Page\CustomerAccountAddress;
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
     * @param CustomerAccountAddress $customerAccountAddress
     * @param Address $address
     * @return void
     */
    public function processAssert(
        CustomerAccountIndex $customerAccountIndex,
        CustomerAccountAddress $customerAccountAddress,
        Address $address
    ) {
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('Address Book');
        $addressRenderer = $this->objectManager->create(
            \Magento\Customer\Test\Block\Address\Renderer::class,
            ['address' => $address, 'type' => 'html']
        );
        $addressToVerify = $addressRenderer->render();

        \PHPUnit_Framework_Assert::assertTrue(
            $addressToVerify == $customerAccountAddress->getDefaultAddressBlock()->getDefaultBillingAddress()
            && $addressToVerify == $customerAccountAddress->getDefaultAddressBlock()->getDefaultShippingAddress(),
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
}
