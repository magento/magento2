<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Customer\Api\AccountManagementInterface as AccountManagement;
use Magento\Customer\Api\AddressRepositoryInterface as CustomerAddressRepository;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\Validator\Exception;
use Magento\Framework\Validator\Factory;
use Magento\Quote\Model\Quote as QuoteEntity;

class CustomerManagement
{
    /**
     * @var CustomerAddressRepository
     */
    private $customerAddressRepository;

    /**
     * @var AccountManagement
     */
    private $accountManagement;

    /**
     * @var Factory
     */
    private $validatorFactory;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @param CustomerAddressRepository $customerAddressRepository
     * @param AccountManagement $accountManagement
     * @param Factory $validatorFactory
     * @param AddressFactory $addressFactory
     */
    public function __construct(
        CustomerAddressRepository $customerAddressRepository,
        AccountManagement $accountManagement,
        Factory $validatorFactory,
        AddressFactory $addressFactory
    ) {
        $this->customerAddressRepository = $customerAddressRepository;
        $this->accountManagement = $accountManagement;
        $this->validatorFactory = $validatorFactory;
        $this->addressFactory = $addressFactory;
    }

    /**
     * Populate customer model
     *
     * @param Quote $quote
     *
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

    /**
     * Validate Quote Addresses
     *
     * @param Quote $quote
     *
     * @return void
     * @throws Exception
     */
    public function validateAddresses(QuoteEntity $quote)
    {
        $addresses = [];
        if ($quote->getBillingAddress()->getCustomerAddressId()) {
            $addresses[] = $this->customerAddressRepository->getById(
                $quote->getBillingAddress()->getCustomerAddressId()
            );
        }
        if ($quote->getShippingAddress()->getCustomerAddressId()) {
            $addresses[] = $this->customerAddressRepository->getById(
                $quote->getShippingAddress()->getCustomerAddressId()
            );
        }
        if (!empty($addresses)) {
            foreach ($addresses as $address) {
                $validator = $this->validatorFactory->createValidator('customer_address', 'save');
                $addressModel = $this->addressFactory->create();
                $addressModel->updateData($address);
                if (!$validator->isValid($addressModel)) {
                    // phpcs:ignore Magento2.Exceptions.DirectThrow
                    throw new Exception(
                        null,
                        null,
                        $validator->getMessages()
                    );
                }
            }
        }
    }
}
