<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Fixture;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Data fixture for customer
 */
class Customer implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'password' => 'password',
        CustomerInterface::ID => null,
        CustomerInterface::CONFIRMATION => null,
        CustomerInterface::CREATED_AT => null,
        CustomerInterface::UPDATED_AT => null,
        CustomerInterface::CREATED_IN => null,
        CustomerInterface::DOB => null,
        CustomerInterface::EMAIL => 'customer%uniqid%@mail.com',
        CustomerInterface::FIRSTNAME => 'Firstname%uniqid%',
        CustomerInterface::GENDER => null,
        CustomerInterface::GROUP_ID => null,
        CustomerInterface::LASTNAME => 'Lastname%uniqid%',
        CustomerInterface::MIDDLENAME => null,
        CustomerInterface::PREFIX => null,
        CustomerInterface::STORE_ID => null,
        CustomerInterface::SUFFIX => null,
        CustomerInterface::TAXVAT => null,
        CustomerInterface::WEBSITE_ID => null,
        CustomerInterface::DEFAULT_BILLING => null,
        CustomerInterface::DEFAULT_SHIPPING => null,
        CustomerInterface::KEY_ADDRESSES => [],
        CustomerInterface::DISABLE_AUTO_GROUP_CHANGE => null,
        CustomerInterface::CUSTOM_ATTRIBUTES => [],
        CustomerInterface::EXTENSION_ATTRIBUTES_KEY => [],
    ];

    private const DEFAULT_DATA_ADDRESS = [
        AddressInterface::ID => null,
        AddressInterface::CUSTOMER_ID => null,
        AddressInterface::REGION => 'Massachusetts',
        AddressInterface::REGION_ID => '32',
        AddressInterface::COUNTRY_ID => 'US',
        AddressInterface::STREET => ['%street_number% Test Street%uniqid%'],
        AddressInterface::COMPANY => null,
        AddressInterface::TELEPHONE => '1234567890',
        AddressInterface::FAX => null,
        AddressInterface::POSTCODE => '02108',
        AddressInterface::CITY => 'Boston',
        AddressInterface::FIRSTNAME => 'Firstname%uniqid%',
        AddressInterface::LASTNAME => 'Lastname%uniqid%',
        AddressInterface::MIDDLENAME => null,
        AddressInterface::PREFIX => null,
        AddressInterface::SUFFIX => null,
        AddressInterface::VAT_ID => null,
        AddressInterface::DEFAULT_BILLING => true,
        AddressInterface::DEFAULT_SHIPPING => true,
        AddressInterface::CUSTOM_ATTRIBUTES => [],
        AddressInterface::EXTENSION_ATTRIBUTES_KEY => [],
    ];

    /**
     * @var ServiceFactory
     */
    private ServiceFactory $serviceFactory;

    /**
     * @var AccountManagementInterface
     */
    private AccountManagementInterface $accountManagement;

    /**
     * @var CustomerRegistry
     */
    private CustomerRegistry $customerRegistry;

    /**
     * @var ProcessorInterface
     */
    private ProcessorInterface $dataProcessor;

    /**
     * @var DataMerger
     */
    private DataMerger $dataMerger;

    /**
     * @param ServiceFactory $serviceFactory
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRegistry $customerRegistry
     * @param ProcessorInterface $dataProcessor
     * @param DataMerger $dataMerger
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        AccountManagementInterface $accountManagement,
        CustomerRegistry $customerRegistry,
        ProcessorInterface $dataProcessor,
        DataMerger $dataMerger
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->accountManagement = $accountManagement;
        $this->customerRegistry = $customerRegistry;
        $this->dataProcessor = $dataProcessor;
        $this->dataMerger = $dataMerger;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as Customer::DEFAULT_DATA.
     * @return DataObject|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function apply(array $data = []): ?DataObject
    {
        $customerSaveService = $this->serviceFactory->create(CustomerRepositoryInterface::class, 'save');
        $data = $this->prepareData($data);
        $passwordHash = $this->accountManagement->getPasswordHash($data['password']);
        unset($data['password']);
        $customerSaveService->execute(
            [
                'customer' => $data,
                'passwordHash' => $passwordHash
            ]
        );
        return $this->customerRegistry->retrieveByEmail($data['email'], $data['website_id']);
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $data->setCustomerId($data->getId());
        $service = $this->serviceFactory->create(CustomerRepositoryInterface::class, 'deleteById');
        $service->execute(
            [
                'customerId' => $data->getId()
            ]
        );
    }

    /**
     * Prepare customer data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = $this->dataMerger->merge(self::DEFAULT_DATA, $data);
        $data[CustomerInterface::KEY_ADDRESSES] = $this->prepareAddresses($data[CustomerInterface::KEY_ADDRESSES]);

        return $this->dataProcessor->process($this, $data);
    }

    /**
     * Prepare customer addresses
     *
     * @param array $data
     * @return array
     */
    private function prepareAddresses(array $data): array
    {
        $addresses = [];
        $default = self::DEFAULT_DATA_ADDRESS;
        $streetNumber = 123;
        foreach ($data as $dataAddress) {
            $dataAddress = $this->dataMerger->merge($default, $dataAddress);
            $placeholders = ['%street_number%' => $streetNumber++];
            $dataAddress[AddressInterface::STREET] = array_map(
                fn ($str) => strtr($str, $placeholders),
                $dataAddress[AddressInterface::STREET]
            );
            $addresses[] = $dataAddress;
            $default[AddressInterface::DEFAULT_BILLING] = false;
            $default[AddressInterface::DEFAULT_SHIPPING] = false;
        }

        return $addresses;
    }
}
