<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Ui;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\AddressRepositoryInterface as AddressRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\Api\SimpleDataObjectConverter;

class InlineEdit extends \Magento\Backend\App\Action
{
    /** @var CustomerRepository */
    protected $customerRepository;

    /** @var AddressRepository */
    protected $addressRepository;

    /** @var JsonFactory */
    protected $jsonFactory;

    /** @var CustomerInterface */
    protected $customer;

    /** @var AddressInterfaceFactory  */
    protected $addressFactory;

    /**
     * @param Context $context
     * @param CustomerRepository $customerRepository
     * @param AddressRepository $addressRepository
     * @param JsonFactory $jsonFactory
     * @param AddressInterfaceFactory $addressFactory
     */
    public function __construct(
        Context $context,
        CustomerRepository $customerRepository,
        AddressRepository $addressRepository,
        JsonFactory $jsonFactory,
        AddressInterfaceFactory $addressFactory
    ) {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->jsonFactory = $jsonFactory;
        $this->addressFactory = $addressFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postData = $this->getRequest()->getParam('items', []);
            foreach (array_keys($postData) as $customerId) {
                $this->setCustomer($this->customerRepository->getById($customerId));
                $this->updateCustomer($this->getData($postData[$customerId], true));
                if ($this->getCustomer()->getDefaultBilling()) {
                    $this->updateAddress($this->getData($postData[$customerId]));
                } else {
                    $this->addNewAddress($this->getData($postData[$customerId]));
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Receive entity(customer|customer_address) data from request
     *
     * @param array $data
     * @param null $isCustomerData
     * @return array
     */
    protected function getData(array $data, $isCustomerData = null)
    {
        $result = [];
        $addressKeys = preg_grep(
            '/^(' . AttributeRepository::BILLING_ADDRESS_PREFIX . '\w+)/',
            array_keys($data),
            $isCustomerData
        );
        $result = array_intersect_key($data, array_flip($addressKeys));
        if ($isCustomerData === null) {
            foreach ($result as $key => $value) {
                if (strpos($key, AttributeRepository::BILLING_ADDRESS_PREFIX) !== false) {
                    unset($result[$key]);
                    $result[str_replace(AttributeRepository::BILLING_ADDRESS_PREFIX, '', $key)] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Update customer data
     *
     * @param array $data
     */
    protected function updateCustomer(array $data)
    {
        $customer = $this->getCustomer();
        $this->setData($customer, $data);
        $this->customerRepository->save($customer);
    }

    /**
     * Update customer address data
     *
     * @param array $data
     */
    protected function updateAddress(array $data)
    {
        $addresses = $this->getCustomer()->getAddresses();
        /** @var \Magento\Customer\Api\Data\AddressInterface $address */
        foreach ($addresses as $address) {
            if ($address->isDefaultBilling()) {
                $this->setData($address, $this->parseStreetField($data));
                $this->addressRepository->save($address);
                break;
            }
        }
    }

    /**
     * Update entity(customer|customer_address) data
     *
     * @param $entity
     * @param $data
     */
    protected function setData($entity, $data)
    {
        foreach ($data as $attributeName => $value) {
            $setterName = 'set' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($attributeName);
            // Check if setter exists
            if (method_exists($entity, $setterName)) {
                call_user_func([$entity, $setterName], $value);
            }
        }
    }

    /**
     * Add new address to customer
     *
     * @param array $data
     */
    protected function addNewAddress(array $data)
    {
        $customer = $this->getCustomer();
        /** @var \Magento\Customer\Api\Data\AddressInterface $address */
        $address = $this->addressFactory->create();
        $this->setData($address, $this->processAddressData($data));
        $address->setIsDefaultBilling(true);
        $address->setCustomerId($customer->getId());
        $this->addressRepository->save($address);
        $customer->setDefaultBilling($address->getId());
    }

    /**
     * Parse street field
     *
     * @param array $data
     * @return array
     */
    protected function processAddressData(array $data)
    {
        if (isset($data['street'])) {
            $data['street'] = explode("\n", $data['street']);
        }
        foreach (['firstname', 'lastname'] as $requiredField) {
            if (empty($data[$requiredField])) {
                $data[$requiredField] =  $this->getCustomer()->{'get' . ucfirst($requiredField)}();
            }
        }
        return $data;
    }

    /**
     * Set customer
     *
     * @param CustomerInterface $customer
     */
    protected function setCustomer(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Receive customer
     *
     * @return CustomerInterface
     */
    protected function getCustomer()
    {
        return $this->customer;
    }
}
