<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Customer\Model\Session;

class CustomerData
{
    /**
     * @var Session
     */
    private $customerSession;

    public function __construct(
        Session $customerSession
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
    private function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }
}
