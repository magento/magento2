<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\AddressInjectable;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerDefaultAddresses
 */
class AssertCustomerDefaultAddresses extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Asserts that Default Billing Address and Default Shipping Address equal to data from fixture
     *
     * @param CustomerAccountIndex $customerAccountIndex
     * @param AddressInjectable $address
     * @return void
     */
    public function processAssert(CustomerAccountIndex $customerAccountIndex, AddressInjectable $address)
    {
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('Account Dashboard');
        $defaultBillingAddress = explode(
            "\n",
            $customerAccountIndex->getDashboardAddress()->getDefaultBillingAddressText()
        );
        $defaultShippingAddress = explode(
            "\n",
            $customerAccountIndex->getDashboardAddress()->getDefaultShippingAddressText()
        );
        $pattern = $this->makeAddressPattern($address);
        $billingDataDiff = $this->verifyForm($pattern, $defaultBillingAddress);
        $shippingDataDiff = $this->verifyForm($pattern, $defaultShippingAddress);
        $dataDiff = array_merge($billingDataDiff, $shippingDataDiff);

        \PHPUnit_Framework_Assert::assertEmpty(
            $dataDiff,
            'Billing or shipping form was filled incorrectly.'
            . "\nLog:\n" . implode(";\n", $dataDiff)
        );
    }

    /**
     * String representation of success assert
     *
     * @return string
     */
    public function toString()
    {
        return 'Default billing and shipping address form is correct.';
    }

    /**
     * Verifying that form is filled correctly
     *
     * @param array $pattern
     * @param array $address
     * @return array
     */
    protected function verifyForm(array $pattern, array $address)
    {
        $errorMessages = [];
        foreach ($pattern as $value) {
            if (!in_array($value, $address)) {
                $errorMessages[] = "Data '$value' in fields is not found.";
            }
        }
        return $errorMessages;
    }

    /**
     * Make pattern for form verifying
     *
     * @param AddressInjectable $address
     * @return array
     */
    protected function makeAddressPattern(AddressInjectable $address)
    {
        $pattern = [];
        $regionId = $address->getRegionId();
        $region = $regionId ? $regionId : $address->getRegion();

        $pattern[] = $address->getFirstname() . " " . $address->getLastname();
        $pattern[] = $address->getCompany();
        $pattern[] = $address->getStreet();
        $pattern[] = $address->getCity() . ", " . $region . ", " . $address->getPostcode();
        $pattern[] = $address->getCountryId();
        $pattern[] = "T: " . $address->getTelephone();
        $pattern[] = "F: " . $address->getFax();
        return $pattern;
    }
}
