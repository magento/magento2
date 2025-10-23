<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Fixture;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Data fixture for customer with multiple addresses
 */
class CustomerWithAddresses implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA_ADDRESS = [
        [
            AddressInterface::ID => null,
            AddressInterface::CUSTOMER_ID => null,
            AddressInterface::REGION => 'California',
            AddressInterface::REGION_ID => '12',
            AddressInterface::COUNTRY_ID => 'US',
            AddressInterface::STREET => ['%street_number% Test Street%uniqid%'],
            AddressInterface::COMPANY => null,
            AddressInterface::TELEPHONE => '1234567893',
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
        ],
        [
            AddressInterface::ID => null,
            AddressInterface::CUSTOMER_ID => null,
            AddressInterface::REGION => 'California',
            AddressInterface::REGION_ID => '12',
            AddressInterface::COUNTRY_ID => 'US',
            AddressInterface::STREET => ['%street_number% Sunset Boulevard%uniqid%'],
            AddressInterface::COMPANY => null,
            AddressInterface::TELEPHONE => '0987654321',
            AddressInterface::FAX => null,
            AddressInterface::POSTCODE => '90001',
            AddressInterface::CITY => 'Los Angeles',
            AddressInterface::FIRSTNAME => 'Firstname%uniqid%',
            AddressInterface::LASTNAME => 'Lastname%uniqid%',
            AddressInterface::MIDDLENAME => null,
            AddressInterface::PREFIX => null,
            AddressInterface::SUFFIX => null,
            AddressInterface::VAT_ID => null,
            AddressInterface::DEFAULT_BILLING => false,
            AddressInterface::DEFAULT_SHIPPING => false,
            AddressInterface::CUSTOM_ATTRIBUTES => [],
            AddressInterface::EXTENSION_ATTRIBUTES_KEY => [],
        ],
        [
            AddressInterface::ID => null,
            AddressInterface::CUSTOMER_ID => null,
            AddressInterface::REGION => 'New York',
            AddressInterface::REGION_ID => '43',
            AddressInterface::COUNTRY_ID => 'US',
            AddressInterface::STREET => ['%street_number% 5th Avenue%uniqid%'],
            AddressInterface::COMPANY => null,
            AddressInterface::TELEPHONE => '1112223333',
            AddressInterface::FAX => null,
            AddressInterface::POSTCODE => '10001',
            AddressInterface::CITY => 'New York City',
            AddressInterface::FIRSTNAME => 'Firstname%uniqid%',
            AddressInterface::LASTNAME => 'Lastname%uniqid%',
            AddressInterface::MIDDLENAME => null,
            AddressInterface::PREFIX => null,
            AddressInterface::SUFFIX => null,
            AddressInterface::VAT_ID => null,
            AddressInterface::DEFAULT_BILLING => false,
            AddressInterface::DEFAULT_SHIPPING => false,
            AddressInterface::CUSTOM_ATTRIBUTES => [],
            AddressInterface::EXTENSION_ATTRIBUTES_KEY => [],
        ]
    ];

    private const DEFAULT_DATA = [
        'password' => 'password',
        CustomerInterface::ID => null,
        CustomerInterface::CREATED_AT => null,
        CustomerInterface::CONFIRMATION => null,
        CustomerInterface::UPDATED_AT => null,
        CustomerInterface::DOB => null,
        CustomerInterface::CREATED_IN => null,
        CustomerInterface::EMAIL => 'customer%uniqid%@mail.com',
        CustomerInterface::FIRSTNAME => 'Firstname%uniqid%',
        CustomerInterface::GROUP_ID => null,
        CustomerInterface::GENDER => null,
        CustomerInterface::LASTNAME => 'Lastname%uniqid%',
        CustomerInterface::MIDDLENAME => null,
        CustomerInterface::PREFIX => null,
        CustomerInterface::SUFFIX => null,
        CustomerInterface::STORE_ID => null,
        CustomerInterface::TAXVAT => null,
        CustomerInterface::WEBSITE_ID => null,
        CustomerInterface::DEFAULT_SHIPPING => null,
        CustomerInterface::DEFAULT_BILLING => null,
        CustomerInterface::DISABLE_AUTO_GROUP_CHANGE => null,
        CustomerInterface::KEY_ADDRESSES => [],
        CustomerInterface::CUSTOM_ATTRIBUTES => [],
        CustomerInterface::EXTENSION_ATTRIBUTES_KEY => [],
    ];

    /**
     * CustomerWithAddresses Constructor
     *
     * @param ServiceFactory $serviceFactory
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRegistry $customerRegistry
     * @param ProcessorInterface $dataProcessor
     * @param DataMerger $dataMerger
     */
    public function __construct(
        private readonly ServiceFactory $serviceFactory,
        private readonly AccountManagementInterface $accountManagement,
        private readonly CustomerRegistry $customerRegistry,
        private readonly DataMerger $dataMerger,
        private readonly ProcessorInterface $dataProcessor
    ) {
    }

    /**
     * Apply the changes for the fixture
     *
     * @param array $data
     * @return DataObject|null
     * @throws NoSuchEntityException
     */
    public function apply(array $data = []): ?DataObject
    {
        $customerSave = $this->serviceFactory->create(CustomerRepositoryInterface::class, 'save');
        $data = $this->prepareCustomerData($data);
        $passwordHash = $this->accountManagement->getPasswordHash($data['password']);
        unset($data['password']);

        $customerSave->execute(
            [
                'customer' => $data,
                'passwordHash' => $passwordHash
            ]
        );

        return $this->customerRegistry->retrieveByEmail($data['email'], $data['website_id']);
    }

    /**
     * Revert the test customer creation
     *
     * @param DataObject $data
     * @return void
     */
    public function revert(DataObject $data): void
    {
        $data->setCustomerId($data->getId());
        $customerService = $this->serviceFactory->create(CustomerRepositoryInterface::class, 'deleteById');

        $customerService->execute(
            [
                'customerId' => $data->getId()
            ]
        );
    }

    /**
     * Prepare customer's  data
     *
     * @param array $data
     * @return array
     */
    private function prepareCustomerData(array $data): array
    {
        $data = $this->dataMerger->merge(self::DEFAULT_DATA, $data);
        $data[CustomerInterface::KEY_ADDRESSES] = $this->prepareAddresses($data[CustomerInterface::KEY_ADDRESSES]);

        return $this->dataProcessor->process($this, $data);
    }

    /**
     * Prepare customer's addresses
     *
     * @param array $data
     * @return array
     */
    private function prepareAddresses(array $data): array
    {
        $addressesData = [];
        $default = self::DEFAULT_DATA_ADDRESS;
        $streetNumber = 123;
        foreach ($default as $address) {
            if ($data) {
                $address = $this->dataMerger->merge($default, $address);
            }
            $placeholders = ['%street_number%' => $streetNumber++];
            $address[AddressInterface::STREET] = array_map(
                fn ($str) => strtr($str, $placeholders),
                $address[AddressInterface::STREET]
            );
            $addressesData[] = $address;
        }

        return $addressesData;
    }
}
