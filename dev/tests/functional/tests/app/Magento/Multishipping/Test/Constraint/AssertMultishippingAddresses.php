<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Multishipping\Test\Page\MultishippingCheckoutShipping;
use Magento\Multishipping\Test\Page\MultishippingEditAddress;

/**
 * Assert that addresses information on edit page is complete and correct.
 */
class AssertMultishippingAddresses extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert that address information on edit page is same as in fixture and complete.
     *
     * @param Customer $customer
     * @param MultishippingCheckoutShipping $checkoutShipping
     * @param MultishippingEditAddress $multishippingEditAddress
     * @return void
     */
    public function processAssert(
        Customer $customer,
        MultishippingCheckoutShipping $checkoutShipping,
        MultishippingEditAddress $multishippingEditAddress
    ) {
        $addresses = $customer->getAddress();
        foreach ($addresses as $address) {
            $checkoutShipping->getShippingBlock()->clickChangeAddress($address['street']);
            $formData = $multishippingEditAddress->getAddressBlock()->getData();
            list($address, $formData) = $this->processData($address, $formData);

            \PHPUnit_Framework_Assert::assertEquals(
                serialize($address),
                serialize($formData),
                'Address does not displayed correctly.'
            );
            $checkoutShipping->open();
        }
    }

    /**
     * Returns string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Addresses information on edit page is complete and correct.';
    }

    /**
     * @param array $address
     * @param array $formData
     * @return array
     */
    private function processData($address, $formData)
    {
        unset($address['email']);
        unset($address['default_billing']);
        unset($address['default_shipping']);
        ksort($address);
        ksort($formData);

        return [$address, $formData];
    }
}
