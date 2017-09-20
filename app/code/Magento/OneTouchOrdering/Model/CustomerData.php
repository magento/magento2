<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;

class CustomerData
{
    /**
     * @var Customer
     */
    private $customer;

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function getDefaultBillingAddressDataModel(): \Magento\Customer\Api\Data\AddressInterface
    {
        return $this->getCustomer()->getDefaultBillingAddress()->getDataModel();
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function getDefaultShippingAddressDataModel(): \Magento\Customer\Api\Data\AddressInterface
    {
        return $this->getCustomer()->getDefaultShippingAddress()->getDataModel();
    }

    /**
     * @param $addressId
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function getShippingAddressDataModel($addressId)
    {
        return $this->getCustomer()->getAddressById($addressId)->getDataModel();
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomerDataModel(): \Magento\Customer\Api\Data\CustomerInterface
    {
        return $this->getCustomer()->getDataModel();
    }

    /**
     * @return int
     */
    public function getCustomerId(): int
    {
        return $this->getCustomer()->getId();
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Customer
     * @throws LocalizedException
     */
    private function getCustomer(): Customer
    {
        if (!$this->customer) {
            throw new LocalizedException(
                __('Something went wrong. Please try again later.')
            );
        }

        return $this->customer;
    }
}
