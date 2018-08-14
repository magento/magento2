<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Customer\Test\Page\CustomerAddressEdit;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Fixture\Address;

/**
 * Assert that deleted customers address is absent in Address Book in Customer Account.
 */
class AssertAddressDeletedFrontend extends AbstractConstraint
{
    /**
     * Asserts that deleted address is not present on Frontend.
     *
     * @param CustomerAccountIndex $customerAccountIndex
     * @param CustomerAddressEdit $customerAddressEdit
     * @param Customer $customer
     * @param Address $addressToDelete
     * @return void
     */
    public function processAssert(
        CustomerAccountIndex $customerAccountIndex,
        CustomerAddressEdit $customerAddressEdit,
        Customer $customer,
        Address $addressToDelete
    ) {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();

        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('Address Book');
        $addressRenderer = $this->objectManager->create(
            \Magento\Customer\Test\Block\Address\Renderer::class,
            ['address' => $addressToDelete, 'type' => 'html']
        );
        $deletedAddress = $addressRenderer->render();

        $isAddressDeleted = false;
        if ($customerAddressEdit->getEditForm()->isVisible()
            || ($customerAccountIndex->getAdditionalAddressBlock()->getBlockText() !== null
            && $deletedAddress != $customerAccountIndex->getAdditionalAddressBlock()->getBlockText())
            || ($customerAccountIndex->getDefaultAddressBlock()->getBlockText() !== null
            && $deletedAddress != $customerAccountIndex->getAdditionalAddressBlock()->getBlockText())
        ) {
            $isAddressDeleted = true;
        }

        \PHPUnit_Framework_Assert::assertTrue(
            $isAddressDeleted,
            'Customer address was not deleted.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Deleted address is absent in Frontend.';
    }
}
