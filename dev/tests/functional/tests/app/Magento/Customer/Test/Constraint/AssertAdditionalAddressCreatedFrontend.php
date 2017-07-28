<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that customers address is preset in Address Book in Customer Account.
 */
class AssertAdditionalAddressCreatedFrontend extends AbstractConstraint
{
    /**
     * Asserts that 'Additional Address Entries' contains expected address.
     *
     * @param CustomerAccountIndex $customerAccountIndex
     * @param Address $shippingAddress
     * @return void
     */
    public function processAssert(CustomerAccountIndex $customerAccountIndex, Address $shippingAddress)
    {
        $customerAccountIndex->open();
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('Address Book');
        $addressRenderer = $this->objectManager->create(
            \Magento\Customer\Test\Block\Address\Renderer::class,
            ['address' => $shippingAddress, 'type' => 'html']
        )->render();
        $isAddressExists = $customerAccountIndex->getAdditionalAddressBlock()
            ->isAdditionalAddressExists($addressRenderer);
        \PHPUnit_Framework_Assert::assertTrue(
            $isAddressExists,
            'Customers address is absent in customer address book.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customers address is absent in customer address book.';
    }
}
