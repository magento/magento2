<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\AccountManagementInterface as AccountManagement;
use Magento\Customer\Api\AddressRepositoryInterface as CustomerAddressRepository;
use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Framework\App\ObjectManager;

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
     * @var \Magento\Framework\Validator\Factory
     * @since 2.2.0
     */
    private $validatorFactory;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     * @since 2.2.0
     */
    private $addressFactory;

    /**
     * CustomerManagement constructor.
     * @param CustomerRepository $customerRepository
     * @param CustomerAddressRepository $customerAddressRepository
     * @param AccountManagement $accountManagement
     * @param \Magento\Framework\Validator\Factory|null $validatorFactory
     * @param \Magento\Customer\Model\AddressFactory|null $addressFactory
     */
    public function __construct(
        CustomerRepository $customerRepository,
        CustomerAddressRepository $customerAddressRepository,
        AccountManagement $accountManagement,
        \Magento\Framework\Validator\Factory $validatorFactory = null,
        \Magento\Customer\Model\AddressFactory $addressFactory = null
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->accountManagement = $accountManagement;
        $this->validatorFactory = $validatorFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Validator\Factory::class);
        $this->addressFactory = $addressFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Customer\Model\AddressFactory::class);
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

    /**
     * Validate Quote Addresses
     *
     * @param Quote $quote
     * @throws \Magento\Framework\Validator\Exception
     * @return void
     * @since 2.2.0
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
                    throw new \Magento\Framework\Validator\Exception(
                        null,
                        null,
                        $validator->getMessages()
                    );
                }
            }
        }
    }
}
