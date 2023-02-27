<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AddressSearchResultsInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterfaceFactory;
use Magento\Customer\Model\Customer\NotificationStorage;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Customer\Model\Data\CustomerSecureFactory;
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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CustomerRepositoryTest extends TestCase
{
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
        $this->customerMetadata = $this->getMockForAbstractClass(
            CustomerMetadataInterface::class,
            [],
            '',
            false
        );
        $this->searchResultsFactory = $this->createPartialMock(
            CustomerSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->eventManager = $this->getMockForAbstractClass(
            ManagerInterface::class,
            [],
            '',
            false
        );
        $this->storeManager = $this->getMockForAbstractClass(
            StoreManagerInterface::class,
            [],
            '',
            false
        );
        $this->extensibleDataObjectConverter = $this->createMock(
            ExtensibleDataObjectConverter::class
        );
        $this->imageProcessor = $this->getMockForAbstractClass(
            ImageProcessorInterface::class,
            [],
            '',
            false
        );
        $this->extensionAttributesJoinProcessor = $this->getMockForAbstractClass(
            JoinProcessorInterface::class,
            [],
            '',
            false
        );
        $this->customer = $this->getMockForAbstractClass(
            CustomerInterface::class,
            [],
            '',
            true,
            true,
            true,
            [
                '__toArray'
            ]
        );
        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();
        $this->notificationStorage = $this->getMockBuilder(NotificationStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

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
            $this->notificationStorage
        );
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSave(): void
    {
        $customerId = 1;

        $customerModel = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)->addMethods(
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
            ]
        )
            ->onlyMethods(['getId', 'setId', 'getAttributeSetId', 'getDataModel', 'save', 'setOrigData'])
            ->disableOriginalConstructor()
            ->getMock();

        $origCustomer = $this->customer;

        $customerAttributesMetaData = $this->getMockForAbstractClass(
            CustomAttributesDataInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'getId',
                'getEmail',
                'getWebsiteId',
                'getAddresses',
                'setAddresses',
                'getGroupId'
            ]
        );
        $customerSecureData = $this->getMockBuilder(CustomerSecure::class)
            ->addMethods(
                [
                    'getRpToken',
                    'getRpTokenCreatedAt',
                    'getPasswordHash',
                    'getFailuresNum',
                    'getFirstFailure',
                    'getLockExpires'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->customer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $this->customer
            ->method('__toArray')
            ->willReturnOnConsecutiveCalls(['firstname' => 'firstname', 'group_id' => 1], []);
        $customerModel->expects($this->exactly(2))
            ->method('setOrigData')
            ->withConsecutive(
                ['firstname', 'firstname'],
                ['group_id', 1]
            );
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

        $customerSecureData = $this->getMockBuilder(CustomerSecure::class)
            ->addMethods(
                [
                    'getRpToken',
                    'getRpTokenCreatedAt',
                    'getPasswordHash',
                    'getFailuresNum',
                    'getFirstFailure',
                    'getLockExpires'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $origCustomer = $this->customer;

        $customerModel = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)->addMethods(
            ['setStoreId', 'getStoreId', 'setAttributeSetId', 'setRpToken', 'setRpTokenCreatedAt', 'setPasswordHash']
        )
            ->onlyMethods(['getId', 'setId', 'getAttributeSetId', 'getDataModel', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerAttributesMetaData = $this->getMockForAbstractClass(
            CustomAttributesDataInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'getId',
                'getEmail',
                'getWebsiteId',
                'getAddresses',
                'setAddresses',
                'getGroupId'
            ]
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
     */
    public function testGetList(): void
    {
        $collection = $this->createMock(Collection::class);
        $searchResults = $this->getMockForAbstractClass(
            AddressSearchResultsInterface::class,
            [],
            '',
            false
        );
        $searchCriteria = $this->getMockForAbstractClass(
            SearchCriteriaInterface::class,
            [],
            '',
            false
        );
        $customerModel = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
            ->onlyMethods(
                [
                    'getId',
                    'setId',
                    'getAttributeSetId',
                    'getDataModel',
                    'getCollection'
                ]
            )
            ->addMethods(
                [
                    'setStoreId',
                    'getStoreId',
                    'setAttributeSetId',
                    'setRpToken',
                    'setRpTokenCreatedAt',
                    'setPasswordHash'
                ]
            )
            ->setMockClassName('customerModel')
            ->disableOriginalConstructor()
            ->getMock();
        $metadata = $this->getMockForAbstractClass(
            AttributeMetadataInterface::class,
            [],
            '',
            false
        );

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
        $this->customerMetadata->expects($this->once())
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
            ->withConsecutive(
                [
                    'billing_postcode',
                    'customer_address/postcode',
                    'default_billing',
                    null,
                    'left'
                ],
                [
                    'billing_city',
                    'customer_address/city',
                    'default_billing',
                    null,
                    'left'
                ],
                [
                    'billing_telephone',
                    'customer_address/telephone',
                    'default_billing',
                    null,
                    'left'
                ],
                [
                    'billing_region',
                    'customer_address/region',
                    'default_billing',
                    null,
                    'left'
                ],
                [
                    'billing_country_id',
                    'customer_address/country_id',
                    'default_billing',
                    null,
                    'left'
                ],
                [
                    'billing_company',
                    'customer_address/company',
                    'default_billing',
                    null,
                    'left'
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $collection,
                $collection,
                $collection,
                $collection,
                $collection,
                $collection
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
