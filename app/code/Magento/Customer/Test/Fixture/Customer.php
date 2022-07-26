<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Customer\Test\Fixture;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerFactory;
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
        'password' => null,
        CustomerInterface::ID => null,
        CustomerInterface::CONFIRMATION => null,
        CustomerInterface::CREATED_AT => null,
        CustomerInterface::UPDATED_AT => null,
        CustomerInterface::CREATED_IN => null,
        CustomerInterface::DOB => null,
        CustomerInterface::EMAIL => 'customer%uniqid%@mail.com',
        CustomerInterface::FIRSTNAME => 'Firstname %uniqid%',
        CustomerInterface::GENDER => null,
        CustomerInterface::GROUP_ID => null,
        CustomerInterface::LASTNAME => 'Lastname %uniqid%',
        CustomerInterface::MIDDLENAME => null,
        CustomerInterface::PREFIX => null,
        CustomerInterface::STORE_ID => null,
        CustomerInterface::SUFFIX => null,
        CustomerInterface::TAXVAT => null,
        CustomerInterface::WEBSITE_ID => null,
        CustomerInterface::DEFAULT_BILLING => null,
        CustomerInterface::DEFAULT_SHIPPING => null,
        CustomerInterface::KEY_ADDRESSES => [],
        CustomerInterface::DISABLE_AUTO_GROUP_CHANGE => null
    ];

    private const DEFAULT_DATA_ADDRESS = [
        AddressInterface::ID => null,
        AddressInterface::CUSTOMER_ID => null,
        AddressInterface::REGION => null,
        AddressInterface::REGION_ID => null,
        AddressInterface::COUNTRY_ID => null,
        AddressInterface::STREET => null,
        AddressInterface::COMPANY => null,
        AddressInterface::TELEPHONE => null,
        AddressInterface::FAX => null,
        AddressInterface::POSTCODE => null,
        AddressInterface::CITY => null,
        AddressInterface::FIRSTNAME => 'Firstname %uniqid%',
        AddressInterface::LASTNAME => 'Lastname %uniqid%',
        AddressInterface::MIDDLENAME => null,
        AddressInterface::PREFIX => null,
        AddressInterface::SUFFIX => null,
        AddressInterface::VAT_ID => null,
        AddressInterface::DEFAULT_BILLING => null,
        AddressInterface::DEFAULT_SHIPPING => null
    ];

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @var DataMerger
     */
    private $dataMerger;

    /**
     * @var null
     */
    private $customer;

    /**
     * @param ServiceFactory $serviceFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerFactory $customerFactory
     * @param CustomerRegistry $customerRegistry
     * @param ProcessorInterface $dataProcessor
     * @param DataMerger $dataMerger
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        CustomerRegistry $customerRegistry,
        ProcessorInterface $dataProcessor,
        DataMerger $dataMerger,
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->customerRegistry = $customerRegistry;
        $this->dataProcessor = $dataProcessor;
        $this->dataMerger = $dataMerger;
        $this->customer = null;
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
        if (count($data[CustomerInterface::KEY_ADDRESSES])) {
            $addresses = $this->prepareCustomerAddress($data[CustomerInterface::KEY_ADDRESSES]);
            $data[CustomerInterface::KEY_ADDRESSES] = $addresses;
        }
        $customerSaveService->execute(
            [
                'customer' => $data,
                'passwordHash' => $this->customer->getPasswordHash()
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
        $data = $this->dataMerger->merge(self::DEFAULT_DATA, $data, false);

        $this->customer = $this->customerFactory->create(['data' => $data]);
        $this->customer->setPassword($data['password']);
        if (isset($data['password'])) {
            unset($data['password']);
        }

        return $this->dataProcessor->process($this, $data);
    }

    /**
     * Prepare customer address
     *
     * @param array $data
     * @return array
     */
    private function prepareCustomerAddress(array $data): array
    {
        $addresses = [];
        foreach ($data as $dataAddress) {
            $dataAddress = $this->dataMerger->merge(self::DEFAULT_DATA_ADDRESS, $dataAddress, false);
            $addresses[] = $this->dataProcessor->process($this, $dataAddress);
        }

        return $addresses;
    }
}
