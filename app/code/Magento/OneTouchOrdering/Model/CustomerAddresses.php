<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Customer\Model\Session as CustomerSession;

class CustomerAddresses
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * @return array
     */
    public function getFormattedAddresses()
    {
        $customer = $this->getCustomer();
        $addresses = $customer->getAddresses();
        $addressesFormatted = [];
        /**
         * @var \Magento\Customer\Model\Address $address
         */
        foreach ($addresses as $address) {
            $addressString = sprintf(
                "%s, %s, %s, %s %s, %s",
                $address->getName(),
                $address->getStreetFull(),
                $address->getCity(),
                $address->getRegion(),
                $address->getPostcode(),
                $address->getCountry()
            );
            $addressesFormatted[] = [
                'address' => $addressString,
                'id' => $address->getId(),
            ];
        }

        return $addressesFormatted;
    }

    /**
     * @return mixed
     */
    public function getDefaultAddressId()
    {
        $customer = $this->getCustomer();
        $address = $customer->getDefaultShippingAddress();
        if (!$address) {
            return $address;
        }

        return $address->getId();
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }
}