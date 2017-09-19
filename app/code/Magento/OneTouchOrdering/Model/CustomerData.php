<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;

class CustomerData
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * CustomerData constructor.
     * @param Session $customerSession
     */
    public function __construct(
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
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
        return $this->customerSession->getCustomerId();
    }

    /**
     * @return Customer
     */
    private function getCustomer(): Customer
    {
        return $this->customerSession->getCustomer();
    }
}
