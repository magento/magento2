<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;

class CustomerDataGetter
{
    /**
     * @var Customer
     */
    private $customer;

    public function __construct(
        Customer $customer
    ) {
        $this->customer = $customer;
    }

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
     * @return Customer
     * @throws LocalizedException
     */
    private function getCustomer(): Customer
    {
        return $this->customer;
    }
}
