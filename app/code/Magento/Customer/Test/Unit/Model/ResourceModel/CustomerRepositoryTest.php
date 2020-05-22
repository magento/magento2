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
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Customer\Model\ResourceModel\CustomerRepository.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CustomerRepositoryTest extends TestCase
{
    /**
     * @var CustomerRepository
     */
    private $model;

    /**
     * @var CustomerFactory|MockObject
     */
    private $customerFactoryMock;

    /**
     * @var CustomerRegistry|MockObject
     */
    private $customerRegistryMock;

    /**
     * @var CustomerMetadataInterface|MockObject
     */
    private $customerMetadataMock;

    /**
     * @var CustomerSearchResultsInterfaceFactory|MockObject
     */
    private $searchResultsFactoryMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var ExtensibleDataObjectConverter|MockObject
     */
    private $extensibleDataObjectConverterMock;

    /**
     * @var ImageProcessorInterface|MockObject
     */
    private $imageProcessorMock;

    /**
     * @var JoinProcessorInterface|MockObject
     */
    private $extensionAttributesJoinProcessorMock;

    /**
     * @var CustomerInterface|MockObject
     */
    private $customerMock;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessorMock;

    /**
     * @var NotificationStorage|MockObject
     */
    private $notificationStorageMock;

    /**
     * @var HydratorInterface|MockObject
     */
    private $hydratorMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->customerRegistryMock = $this->createMock(CustomerRegistry::class);
        $this->customerFactoryMock = $this->createPartialMock(CustomerFactory::class, ['create']);
        $this->customerMetadataMock = $this->getMockForAbstractClass(
            CustomerMetadataInterface::class,
            [],
            '',
            false
        );
        $this->searchResultsFactoryMock = $this->createPartialMock(
            CustomerSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->eventManagerMock = $this->getMockForAbstractClass(
            ManagerInterface::class,
            [],
            '',
            false
        );
        $this->extensibleDataObjectConverterMock =
            $this->createMock(ExtensibleDataObjectConverter::class);
        $this->imageProcessorMock = $this->getMockForAbstractClass(
            ImageProcessorInterface::class,
            [],
            '',
            false
        );
        $this->extensionAttributesJoinProcessorMock = $this->getMockForAbstractClass(
            JoinProcessorInterface::class,
            [],
            '',
            false
        );
        $this->customerMock = $this->getMockForAbstractClass(
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
        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)->getMock();
        $this->notificationStorageMock = $this->getMockBuilder(NotificationStorage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->hydratorMock = $this->createMock(HydratorInterface::class);

        $this->model = $objectManager->getObject(
            CustomerRepository::class,
            [
                'customerFactory' => $this->customerFactoryMock,
                'customerRegistry' => $this->customerRegistryMock,
                'customerMetadata' => $this->customerMetadataMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'eventManager' => $this->eventManagerMock,
                'extensibleDataObjectConverter' => $this->extensibleDataObjectConverterMock,
                'imageProcessor' => $this->imageProcessorMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'collectionProcessor' => $this->collectionProcessorMock,
                'notificationStorage' => $this->notificationStorageMock,
                'hydrator' => $this->hydratorMock
            ]
        );
    }

    /**
     * Test save customer
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSave(): void
    {
        $customerId = 1;

        $customerModel = $this->getMockBuilder(CustomerModel::class)->addMethods(
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
                'setGroupId'
            ]
        )
            ->onlyMethods(['getId', 'setId', 'getAttributeSetId', 'getDataModel', 'save'])
            ->disableOriginalConstructor()
            ->getMock();

        $origCustomer = $this->customerMock;

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
                'setAddresses'
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
        $this->customerMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $this->customerMock->expects($this->once())
            ->method('__toArray')
            ->willReturn([]);
        $this->hydratorMock->expects($this->at(0))
            ->method('extract')
            ->willReturn(['group_id' => 1]);
        $this->hydratorMock->expects($this->exactly(2))
            ->method('extract')
            ->willReturn([]);
        $this->hydratorMock->expects($this->once())
            ->method('hydrate')
            ->willReturn($this->customerMock);
        $customerModel->expects($this->once())
            ->method('setGroupId')
            ->with(1);
        $this->customerRegistryMock->expects($this->atLeastOnce())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($customerModel);
        $customerModel->expects($this->atLeastOnce())
            ->method('getDataModel')
            ->willReturn($this->customerMock);
        $this->imageProcessorMock->expects($this->once())
            ->method('save')
            ->with($this->customerMock, CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $this->customerMock)
            ->willReturn($customerAttributesMetaData);
        $this->customerRegistryMock->expects($this->atLeastOnce())
            ->method("remove")
            ->with($customerId);
        $this->extensibleDataObjectConverterMock->expects($this->once())
            ->method('toNestedArray')
            ->with($customerAttributesMetaData, [], CustomerInterface::class)
            ->willReturn(['customerData']);
        $this->customerFactoryMock->expects($this->once())
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
        $this->customerRegistryMock->expects($this->once())
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
                    [null, $customerModel],
                ]
            );
        $customerModel->expects($this->once())
            ->method('setRpTokenCreatedAt')
            ->willReturnMap(
                [
                    ['rpTokenCreatedAt', $customerModel],
                    [null, $customerModel],
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
        $this->customerRegistryMock->expects($this->once())
            ->method('push')
            ->with($customerModel);
        $customerAttributesMetaData->expects($this->once())
            ->method('getEmail')
            ->willReturn('example@example.com');
        $customerAttributesMetaData->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(2);
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveByEmail')
            ->with('example@example.com', 2)
            ->willReturn($customerModel);
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'customer_save_after_data_object',
                [
                    'customer_data_object' => $this->customerMock,
                    'orig_customer_data_object' => $origCustomer,
                    'delegate_data' => [],
                ]
            );

        $this->model->save($this->customerMock);
    }

    /**
     * Test save customer with password hash
     *
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
        $origCustomer = $this->customerMock;

        $customerModel = $this->getMockBuilder(CustomerModel::class)->addMethods(
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
                'setAddresses'
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
        $this->customerRegistryMock->expects($this->atLeastOnce())
            ->method('remove')
            ->with($customerId);

        $this->customerRegistryMock->expects($this->once())
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
        $this->customerMock->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $this->customerMock->expects($this->once())
            ->method('__toArray')
            ->willReturn([]);
        $this->hydratorMock->expects($this->atLeastOnce())
            ->method('extract')
            ->willReturn([]);
        $this->hydratorMock->expects($this->atLeastOnce())
            ->method('hydrate')
            ->willReturn($this->customerMock);
        $this->customerRegistryMock->expects($this->atLeastOnce())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($customerModel);
        $customerModel->expects($this->atLeastOnce())
            ->method('getDataModel')
            ->willReturn($this->customerMock);
        $this->imageProcessorMock->expects($this->once())
            ->method('save')
            ->with($this->customerMock, CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $this->customerMock)
            ->willReturn($customerAttributesMetaData);
        $customerAttributesMetaData
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $this->extensibleDataObjectConverterMock->expects($this->once())
            ->method('toNestedArray')
            ->with($customerAttributesMetaData, [], CustomerInterface::class)
            ->willReturn(['customerData']);
        $this->customerFactoryMock->expects($this->once())
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
        $this->customerRegistryMock->expects($this->once())
            ->method('push')
            ->with($customerModel);
        $customerAttributesMetaData->expects($this->once())
            ->method('getEmail')
            ->willReturn('example@example.com');
        $customerAttributesMetaData->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(2);
        $this->customerRegistryMock->expects($this->once())
            ->method('retrieveByEmail')
            ->with('example@example.com', 2)
            ->willReturn($customerModel);
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with(
                'customer_save_after_data_object',
                [
                    'customer_data_object' => $this->customerMock,
                    'orig_customer_data_object' => $origCustomer,
                    'delegate_data' => [],
                ]
            );

        $this->model->save($this->customerMock, $passwordHash);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
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
        $customerModel = $this->getMockBuilder(CustomerModel::class)
            ->setMethods(
                [
                    'getId',
                    'setId',
                    'setStoreId',
                    'getStoreId',
                    'getAttributeSetId',
                    'setAttributeSetId',
                    'setRpToken',
                    'setRpTokenCreatedAt',
                    'getDataModel',
                    'setPasswordHash',
                    'getCollection'
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

        $this->searchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResults);
        $searchResults->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteria);
        $this->customerFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($customerModel);
        $customerModel->expects($this->once())
            ->method('getCollection')
            ->willReturn($collection);
        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collection, CustomerInterface::class);
        $this->customerMetadataMock->expects($this->once())
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
        $collection->expects($this->at(2))
            ->method('joinAttribute')
            ->with('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->willReturnSelf();
        $collection->expects($this->at(3))
            ->method('joinAttribute')
            ->with('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->willReturnSelf();
        $collection->expects($this->at(4))
            ->method('joinAttribute')
            ->with('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->willReturnSelf();
        $collection->expects($this->at(5))
            ->method('joinAttribute')
            ->with('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->willReturnSelf();
        $collection->expects($this->at(6))
            ->method('joinAttribute')
            ->with('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
            ->willReturnSelf();
        $collection->expects($this->at(7))
            ->method('joinAttribute')
            ->with('billing_company', 'customer_address/company', 'default_billing', null, 'left')
            ->willReturnSelf();
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
            ->willReturn($this->customerMock);
        $searchResults->expects($this->once())
            ->method('setItems')
            ->with([$this->customerMock]);

        $this->assertSame($searchResults, $this->model->getList($searchCriteria));
    }

    /**
     * Test delete customer by id
     *
     * @return void
     */
    public function testDeleteById(): void
    {
        $customerId = 14;
        $customerModel = $this->createPartialMock(CustomerModel::class, ['delete']);
        $this->customerRegistryMock
            ->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($customerModel);
        $customerModel->expects($this->once())
            ->method('delete');
        $this->customerRegistryMock->expects($this->atLeastOnce())
            ->method('remove')
            ->with($customerId);

        $this->assertTrue($this->model->deleteById($customerId));
    }

    /**
     * Test delete customer
     *
     * @return void
     */
    public function testDelete(): void
    {
        $customerId = 14;
        $customerModel = $this->createPartialMock(CustomerModel::class, ['delete']);

        $this->customerMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $this->customerRegistryMock
            ->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($customerModel);
        $customerModel->expects($this->once())
            ->method('delete');
        $this->customerRegistryMock->expects($this->atLeastOnce())
            ->method('remove')
            ->with($customerId);
        $this->notificationStorageMock->expects($this->atLeastOnce())
            ->method('remove')
            ->with(NotificationStorage::UPDATE_CUSTOMER_SESSION, $customerId);

        $this->assertTrue($this->model->delete($this->customerMock));
    }
}
