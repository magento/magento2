<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AddressSearchResultsInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Customer\Model\Data\CustomerSecureFactory;
use Magento\Customer\Model\Delegation\Storage as DelegatedStorage;
use Magento\Customer\Model\ResourceModel\AddressRepository;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CustomerRepositoryTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var CustomerFactory|MockObject
     */
    private $customerFactory;

    /**
     * @var CustomerSecureFactory|MockObject
     */
    private $customerSecureFactory;

    /**
     * @var CustomerRegistry|MockObject
     */
    private $customerRegistry;

    /**
     * @var AddressRepository|MockObject
     */
    private $addressRepository;

    /**
     * @var Customer|MockObject
     */
    private $customerResourceModel;

    /**
     * @var CustomerMetadataInterface|MockObject
     */
    private $customerMetadata;

    /**
     * @var CustomerSearchResultsInterfaceFactory|MockObject
     */
    private $searchResultsFactory;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManager;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var ExtensibleDataObjectConverter|MockObject
     */
    private $extensibleDataObjectConverter;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelper;

    /**
     * @var ImageProcessorInterface|MockObject
     */
    private $imageProcessor;

    /**
     * @var JoinProcessorInterface|MockObject
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var CustomerInterface|MockObject
     */
    private $customer;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessorMock;

    /**
     * @var NotificationStorage|MockObject
     */
    private $notificationStorage;

    /**
     * @var DelegatedStorage|MockObject
     */
    private $delegatedStorage;

    /**
     * @var GroupRepositoryInterface|MockObject
     */
    private $groupRepository;

    /**
     * @var CustomerRepository
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->customerResourceModel =
            $this->createMock(Customer::class);
        $this->customerRegistry = $this->createMock(CustomerRegistry::class);
        $this->dataObjectHelper = $this->createMock(DataObjectHelper::class);
        $this->customerFactory =
            $this->createPartialMock(CustomerFactory::class, ['create']);
        $this->customerSecureFactory = $this->createPartialMock(
            CustomerSecureFactory::class,
            ['create']
        );
        $this->addressRepository = $this->createMock(AddressRepository::class);
        $this->customerMetadata = $this->createMock(CustomerMetadataInterface::class);
        $this->searchResultsFactory = $this->createPartialMock(
            CustomerSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->eventManager = $this->createMock(ManagerInterface::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->extensibleDataObjectConverter = $this->createMock(
            ExtensibleDataObjectConverter::class
        );
        $this->imageProcessor = $this->createMock(ImageProcessorInterface::class);
        $this->extensionAttributesJoinProcessor = $this->createMock(JoinProcessorInterface::class);
        $this->customer = $this->createPartialMockWithReflection(
            \Magento\Customer\Model\Data\Customer::class,
            ['__toArray', 'getId', 'getEmail', 'getWebsiteId', 'setWebsiteId', 'setStoreId',
             'getFirstname', 'getLastname', 'getStoreId', 'getAddresses']
        );
        $this->collectionProcessorMock = $this->createMock(CollectionProcessorInterface::class);
        $this->notificationStorage = $this->createMock(NotificationStorage::class);
        $this->delegatedStorage = $this->createMock(DelegatedStorage::class);
        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);

        $this->model = new CustomerRepository(
            $this->customerFactory,
            $this->customerSecureFactory,
            $this->customerRegistry,
            $this->addressRepository,
            $this->customerResourceModel,
            $this->customerMetadata,
            $this->searchResultsFactory,
            $this->eventManager,
            $this->storeManager,
            $this->extensibleDataObjectConverter,
            $this->dataObjectHelper,
            $this->imageProcessor,
            $this->extensionAttributesJoinProcessor,
            $this->collectionProcessorMock,
            $this->notificationStorage,
            $this->delegatedStorage,
            $this->groupRepository
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSave(): void
    {
        $customerId = 1;

        $customerModel = $this->createPartialMockWithReflection(
            \Magento\Customer\Model\Customer::class,
            [
                'setStoreId',
                'getStoreId',
                'setAttributeSetId',
                'setRpToken',
                'setRpTokenCreatedAt',
                'setPasswordHash',
                'setFailuresNum',
                'setFirstFailure',
                'setLockExpires',
                'setGroupId',
                'getId',
                'setId',
                'getAttributeSetId',
                'getDataModel',
                'save',
                'setOrigData'
            ]
        );

        $origCustomer = $this->customer;

        $customerAttributesMetaData = $this->createPartialMockWithReflection(
            \Magento\Customer\Model\Data\Customer::class,
            ['getId', 'getEmail', 'getWebsiteId', 'getAddresses', 'setAddresses', 'getGroupId']
        );
        $customerSecureData = $this->createPartialMockWithReflection(
            CustomerSecure::class,
            [
                    'getRpToken',
                    'getRpTokenCreatedAt',
                    'getPasswordHash',
                    'getFailuresNum',
                    'getFirstFailure',
                    'getLockExpires'
                ]
        );
        $this->customer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $this->customer
            ->method('__toArray')
            ->willReturn(['firstname' => 'firstname', 'group_id' => 1]);
        $customerModel->expects($this->exactly(2))
            ->method('setOrigData')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 == 'firstname' && $arg2 == 'firstname') {
                    return null;
                } elseif ($arg1 == 'group_id' && $arg2 == 1) {
                    return null;
                }
            });
        $this->customerRegistry->expects($this->atLeastOnce())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($customerModel);
        $customerModel->expects($this->atLeastOnce())
            ->method('getDataModel')
            ->willReturn($this->customer);
        $this->imageProcessor->expects($this->once())
            ->method('save')
            ->with($this->customer, CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $this->customer)
            ->willReturn($customerAttributesMetaData);
        $this->customerRegistry->expects($this->atLeastOnce())
            ->method("remove")
            ->with($customerId);
        $this->extensibleDataObjectConverter->expects($this->once())
            ->method('toNestedArray')
            ->with($customerAttributesMetaData, [], CustomerInterface::class)
            ->willReturn(['customerData']);
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->with(['data' => ['customerData']])
            ->willReturn($customerModel);
        $customerModel->expects($this->once())
            ->method('getStoreId')
            ->willReturn(null);
        $customerModel->expects($this->once())
            ->method('setId')
            ->with($customerId);
        $customerAttributesMetaData->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $this->customerRegistry->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($customerSecureData);
        $customerSecureData->expects($this->once())
            ->method('getRpToken')
            ->willReturn('rpToken');
        $customerSecureData->expects($this->once())
            ->method('getRpTokenCreatedAt')
            ->willReturn('rpTokenCreatedAt');
        $customerSecureData->expects($this->once())
            ->method('getPasswordHash')
            ->willReturn('passwordHash');
        $customerSecureData->expects($this->once())
            ->method('getFailuresNum')
            ->willReturn('failuresNum');
        $customerSecureData->expects($this->once())
            ->method('getFirstFailure')
            ->willReturn('firstFailure');
        $customerSecureData->expects($this->once())
            ->method('getLockExpires')
            ->willReturn('lockExpires');

        $customerModel->expects($this->once())
            ->method('setRpToken')
            ->willReturnMap(
                [
                    ['rpToken', $customerModel],
                    [null, $customerModel]
                ]
            );
        $customerModel->expects($this->once())
            ->method('setRpTokenCreatedAt')
            ->willReturnMap(
                [
                    ['rpTokenCreatedAt', $customerModel],
                    [null, $customerModel]
                ]
            );

        $customerModel->expects($this->once())
            ->method('setPasswordHash')
            ->with('passwordHash');
        $customerModel->expects($this->once())
            ->method('setFailuresNum')
            ->with('failuresNum');
        $customerModel->expects($this->once())
            ->method('setFirstFailure')
            ->with('firstFailure');
        $customerModel->expects($this->once())
            ->method('setLockExpires')
            ->with('lockExpires');
        $customerModel->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $customerModel->expects($this->once())
            ->method('save');
        $this->customerRegistry->expects($this->once())
            ->method('push')
            ->with($customerModel);
        $customerAttributesMetaData->expects($this->once())
            ->method('getEmail')
            ->willReturn('example@example.com');
        $customerAttributesMetaData->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(2);
        $this->customerRegistry->expects($this->once())
            ->method('retrieveByEmail')
            ->with('example@example.com', 2)
            ->willReturn($customerModel);
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with(
                'customer_save_after_data_object',
                [
                    'customer_data_object' => $this->customer,
                    'orig_customer_data_object' => $origCustomer,
                    'delegate_data' => []
                ]
            );

        $this->model->save($this->customer);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSaveWithPasswordHash(): void
    {
        $customerId = 1;
        $passwordHash = 'ukfa4sdfa56s5df02asdf4rt';

        $customerSecureData = $this->createPartialMockWithReflection(
            CustomerSecure::class,
            [
                    'getRpToken',
                    'getRpTokenCreatedAt',
                    'getPasswordHash',
                    'getFailuresNum',
                    'getFirstFailure',
                    'getLockExpires'
                ]
        );
        $origCustomer = $this->customer;

        $customerModel = $this->createPartialMockWithReflection(
            \Magento\Customer\Model\Customer::class,
            [
                'setStoreId',
                'getStoreId',
                'setAttributeSetId',
                'setRpToken',
                'setRpTokenCreatedAt',
                'setPasswordHash',
                'getId',
                'setId',
                'getAttributeSetId',
                'getDataModel',
                'save'
            ]
        );
        $customerAttributesMetaData = $this->createPartialMockWithReflection(
            \Magento\Customer\Model\Data\Customer::class,
            ['getId', 'getEmail', 'getWebsiteId', 'getAddresses', 'setAddresses', 'getGroupId']
        );
        $customerModel->expects($this->atLeastOnce())
            ->method('setRpToken')
            ->with(null);
        $customerModel->expects($this->atLeastOnce())
            ->method('setRpTokenCreatedAt')
            ->with(null);
        $customerModel->expects($this->atLeastOnce())
            ->method('setPasswordHash')
            ->with($passwordHash);
        $this->customerRegistry->expects($this->atLeastOnce())
            ->method('remove')
            ->with($customerId);

        $this->customerRegistry->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($customerSecureData);
        $customerSecureData->expects($this->never())
            ->method('getRpToken')
            ->willReturn('rpToken');
        $customerSecureData->expects($this->never())
            ->method('getRpTokenCreatedAt')
            ->willReturn('rpTokenCreatedAt');
        $customerSecureData->expects($this->never())
            ->method('getPasswordHash')
            ->willReturn('passwordHash');
        $customerSecureData->expects($this->once())
            ->method('getFailuresNum')
            ->willReturn('failuresNum');
        $customerSecureData->expects($this->once())
            ->method('getFirstFailure')
            ->willReturn('firstFailure');
        $customerSecureData->expects($this->once())
            ->method('getLockExpires')
            ->willReturn('lockExpires');
        $this->customer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $this->customer->expects($this->atLeastOnce())
            ->method('__toArray')
            ->willReturn([]);
        $this->customerRegistry->expects($this->atLeastOnce())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($customerModel);
        $customerModel->expects($this->atLeastOnce())
            ->method('getDataModel')
            ->willReturn($this->customer);
        $this->imageProcessor->expects($this->once())
            ->method('save')
            ->with($this->customer, CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $this->customer)
            ->willReturn($customerAttributesMetaData);
        $customerAttributesMetaData
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $this->extensibleDataObjectConverter->expects($this->once())
            ->method('toNestedArray')
            ->with($customerAttributesMetaData, [], CustomerInterface::class)
            ->willReturn(['customerData']);
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->with(['data' => ['customerData']])
            ->willReturn($customerModel);
        $customerModel->expects($this->once())
            ->method('setId')
            ->with($customerId);
        $customerModel->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $customerModel->expects($this->once())
            ->method('save');
        $this->customerRegistry->expects($this->once())
            ->method('push')
            ->with($customerModel);
        $customerAttributesMetaData->expects($this->once())
            ->method('getEmail')
            ->willReturn('example@example.com');
        $customerAttributesMetaData->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(2);
        $this->customerRegistry->expects($this->once())
            ->method('retrieveByEmail')
            ->with('example@example.com', 2)
            ->willReturn($customerModel);
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with(
                'customer_save_after_data_object',
                [
                    'customer_data_object' => $this->customer,
                    'orig_customer_data_object' => $origCustomer,
                    'delegate_data' => []
                ]
            );

        $this->model->save($this->customer, $passwordHash);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testGetList(): void
    {
        $collection = $this->createMock(Collection::class);
        $searchResults = $this->createMock(AddressSearchResultsInterface::class);
        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $customerModel = $this->createPartialMockWithReflection(
            \Magento\Customer\Model\Customer::class,
            [
                'getId',
                'setId',
                'getAttributeSetId',
                'getDataModel',
                'getCollection',
                'setStoreId',
                'getStoreId',
                'setAttributeSetId',
                'setRpToken',
                'setRpTokenCreatedAt',
                'setPasswordHash'
            ]
        );
        $metadata = $this->createMock(AttributeMetadataInterface::class);

        $this->searchResultsFactory->expects($this->once())
            ->method('create')
            ->willReturn($searchResults);
        $searchResults->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteria);
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->willReturn($customerModel);
        $customerModel->expects($this->once())
            ->method('getCollection')
            ->willReturn($collection);
        $this->extensionAttributesJoinProcessor->expects($this->once())
            ->method('process')
            ->with($collection, CustomerInterface::class);
        $this->customerMetadata->expects($this->atLeastOnce())
            ->method('getAllAttributesMetadata')
            ->willReturn([$metadata]);
        $metadata->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('attribute-code');
        $collection->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('attribute-code');
        $collection->expects($this->once())
            ->method('addNameToSelect');
        $collection
            ->method('joinAttribute')
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3, $arg4, $arg5) use ($collection) {
                    if ($arg1 == 'billing_postcode' &&
                        $arg2 == 'customer_address/postcode' &&
                        $arg3 == 'default_billing' &&
                        $arg4 == null && $arg5 == 'left') {
                        return $collection;
                    } elseif ($arg1 == 'billing_city' &&
                        $arg2 == 'customer_address/city' &&
                        $arg3 == 'default_billing' &&
                        $arg4 == null && $arg5 == 'left') {
                        return $collection;
                    } elseif ($arg1 == 'billing_telephone' &&
                        $arg2 == 'customer_address/telephone' &&
                        $arg3 == 'default_billing' &&
                        $arg4 == null && $arg5 == 'left') {
                        return $collection;
                    } elseif ($arg1 == 'billing_region' &&
                        $arg2 == 'customer_address/region' &&
                        $arg3 == 'default_billing' &&
                        $arg4 == null && $arg5 == 'left') {
                        return $collection;
                    } elseif ($arg1 == 'billing_country_id' &&
                        $arg2 == 'customer_address/country_id' &&
                        $arg3 == 'default_billing' &&
                        $arg4 == null && $arg5 == 'left') {
                        return $collection;
                    } elseif ($arg1 == 'billing_company' &&
                        $arg2 == 'customer_address/company' &&
                        $arg3 == 'default_billing' &&
                        $arg4 == null && $arg5 == 'left') {
                        return $collection;
                    }
                }
            );
        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);
        $collection->expects($this->once())
            ->method('getSize')
            ->willReturn(23);
        $searchResults->expects($this->once())
            ->method('setTotalCount')
            ->with(23);
        $collection->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$customerModel]));
        $customerModel->expects($this->atLeastOnce())
            ->method('getDataModel')
            ->willReturn($this->customer);
        $searchResults->expects($this->once())
            ->method('setItems')
            ->with([$this->customer]);

        $this->assertSame($searchResults, $this->model->getList($searchCriteria));
    }

    /**
     * @return void
     */
    public function testDeleteById(): void
    {
        $customerId = 14;
        $customerModel = $this->createPartialMock(\Magento\Customer\Model\Customer::class, ['delete']);
        $this->customerRegistry
            ->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($customerModel);
        $customerModel->expects($this->once())
            ->method('delete');
        $this->customerRegistry->expects($this->atLeastOnce())
            ->method('remove')
            ->with($customerId);

        $this->assertTrue($this->model->deleteById($customerId));
    }

    /**
     * @return void
     */
    public function testDelete(): void
    {
        $customerId = 14;
        $customerModel = $this->createPartialMock(\Magento\Customer\Model\Customer::class, ['delete']);

        $this->customer->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $this->customerRegistry
            ->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($customerModel);
        $customerModel->expects($this->once())
            ->method('delete');
        $this->customerRegistry->expects($this->atLeastOnce())
            ->method('remove')
            ->with($customerId);
        $this->notificationStorage->expects($this->atLeastOnce())
            ->method('remove')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customerId);

        $this->assertTrue($this->model->delete($this->customer));
    }
}
