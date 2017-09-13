<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

class CustomerData
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function getDefaultBillingAddressDataModel()
    {
        return $this->getCustomer()->getDefaultBillingAddress()->getDataModel();
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function getDefaultShippingAddressDataModel()
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
    public function getCustomerDataModel()
    {
        return $this->getCustomer()->getDataModel();
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerSession->getCustomerId();
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }
}
