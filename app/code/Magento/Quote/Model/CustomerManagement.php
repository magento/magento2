<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\AccountManagementInterface as AccountManagement;
use Magento\Customer\Api\AddressRepositoryInterface as CustomerAddressRepository;
use Magento\Quote\Model\Quote as QuoteEntity;

/**
 * Class Customer
 */
class CustomerManagement
{
    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var CustomerAddressRepository
     */
    protected $customerAddressRepository;

    /**
     * @var AccountManagement
     */
    protected $accountManagement;

    /**
     * @param CustomerRepository $customerRepository
     * @param CustomerAddressRepository $customerAddressRepository
     * @param AccountManagement $accountManagement
     */
    public function __construct(
        CustomerRepository $customerRepository,
        CustomerAddressRepository $customerAddressRepository,
        AccountManagement $accountManagement
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->accountManagement = $accountManagement;
    }

    /**
     * Populate customer model
     *
     * @param Quote $quote
     * @return void
     */
    public function populateCustomerInfo(QuoteEntity $quote)
    {
        $customer = $quote->getCustomer();

        if (!$customer->getId()) {
            $customer = $this->accountManagement->createAccountWithPasswordHash(
                $customer,
                $quote->getPasswordHash()
            );
            $quote->setCustomer($customer);
        }
        if (!$quote->getBillingAddress()->getId() && $customer->getDefaultBilling()) {
            $quote->getBillingAddress()->importCustomerAddressData(
                $this->customerAddressRepository->getById($customer->getDefaultBilling())
            );
            $quote->getBillingAddress()->setCustomerAddressId($customer->getDefaultBilling());
        }
        if (!$quote->getShippingAddress()->getSameAsBilling()
            && !$quote->getBillingAddress()->getId()
            && $customer->getDefaultShipping()
        ) {
            $quote->getShippingAddress()->importCustomerAddressData(
                $this->customerAddressRepository->getById($customer->getDefaultShipping())
            );
            $quote->getShippingAddress()->setCustomerAddressId($customer->getDefaultShipping());
        }
    }
}
