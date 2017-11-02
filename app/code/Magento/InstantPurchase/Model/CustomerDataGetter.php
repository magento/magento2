<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CustomerDataGetter
 * @api
 */
class CustomerDataGetter
{
    /**
     * @var Customer
     */
    private $customer;

    /**
     * CustomerDataGetter constructor.
     * @param Customer $customer
     */
    public function __construct(
        Customer $customer
    ) {
        $this->customer = $customer;
    }

    /**
     * @return AddressInterface
     */
    public function getDefaultBillingAddressDataModel(): AddressInterface
    {
        return $this->getCustomer()->getDefaultBillingAddress()->getDataModel();
    }

    /**
     * @return AddressInterface
     */
    public function getDefaultShippingAddressDataModel(): AddressInterface
    {
        return $this->getCustomer()->getDefaultShippingAddress()->getDataModel();
    }

    /**
     * @param $addressId
     * @return AddressInterface
     */
    public function getShippingAddressDataModel($addressId)
    {
        return $this->getCustomer()->getAddressById($addressId)->getDataModel();
    }

    /**
     * @return CustomerInterface
     */
    public function getCustomerDataModel(): CustomerInterface
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
