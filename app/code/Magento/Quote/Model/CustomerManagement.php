<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Customer\Api\AccountManagementInterface as AccountManagement;
use Magento\Customer\Api\AddressRepositoryInterface as CustomerAddressRepository;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Validator\Factory as ValidatorFactory;
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
     * @var ValidatorFactory
     */
    private $validatorFactory;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var AddressInterfaceFactory
     */
    private $customerAddressFactory;

    /**
     * CustomerManagement constructor.
     * @param CustomerRepository $customerRepository
     * @param CustomerAddressRepository $customerAddressRepository
     * @param AccountManagement $accountManagement
     * @param AddressInterfaceFactory $customerAddressFactory
     * @param ValidatorFactory|null $validatorFactory
     * @param AddressFactory|null $addressFactory
     */
    public function __construct(
        CustomerRepository $customerRepository,
        CustomerAddressRepository $customerAddressRepository,
        AccountManagement $accountManagement,
        AddressInterfaceFactory $customerAddressFactory,
        ?ValidatorFactory $validatorFactory = null,
        ?AddressFactory $addressFactory = null
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->accountManagement = $accountManagement;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->validatorFactory = $validatorFactory ?: ObjectManager::getInstance()
            ->get(ValidatorFactory::class);
        $this->addressFactory = $addressFactory ?: ObjectManager::getInstance()
            ->get(AddressFactory::class);
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
            $this->fillCustomerAddressId($quote);
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
     * Filling 'CustomerAddressId' in quote for a newly created customer.
     *
     * @param QuoteEntity $quote
     * @return void
     */
    private function fillCustomerAddressId(QuoteEntity $quote): void
    {
        $customer = $quote->getCustomer();

        $customer->getDefaultBilling() ?
            $quote->getBillingAddress()->setCustomerAddressId($customer->getDefaultBilling()) :
            $quote->getBillingAddress()->setCustomerAddressId(0);

        if ($customer->getDefaultShipping() || $customer->getDefaultBilling()) {
            if ($quote->getShippingAddress()->getSameAsBilling()) {
                $quote->getShippingAddress()->setCustomerAddressId($customer->getDefaultBilling());
            } else {
                $quote->getShippingAddress()->setCustomerAddressId($customer->getDefaultShipping());
            }
        } else {
            $quote->getShippingAddress()->setCustomerAddressId(0);
        }
    }

    /**
     * Validate Quote Addresses
     *
     * @param Quote $quote
     * @throws ValidatorException
     * @return void
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
        if (empty($addresses) && $quote->getCustomerIsGuest()) {
            $billingAddress = $quote->getBillingAddress();
            $customerAddress = $this->customerAddressFactory->create();
            $customerAddress->setFirstname($billingAddress->getFirstname());
            $customerAddress->setLastname($billingAddress->getLastname());
            $customerAddress->setStreet($billingAddress->getStreet());
            $customerAddress->setCity($billingAddress->getCity());
            $customerAddress->setPostcode($billingAddress->getPostcode());
            $customerAddress->setTelephone($billingAddress->getTelephone());
            $customerAddress->setCountryId($billingAddress->getCountryId());
            $customerAddress->setCustomAttributes($billingAddress->getCustomAttributes());
            $addresses[] = $customerAddress;
        }
        foreach ($addresses as $address) {
            $validator = $this->validatorFactory->createValidator('customer_address', 'save');
            $addressModel = $this->addressFactory->create();
            $addressModel->updateData($address);
            if (!$validator->isValid($addressModel)) {
                throw new ValidatorException(
                    null,
                    null,
                    $validator->getMessages()
                );
            }
        }
    }
}
