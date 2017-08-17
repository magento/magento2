<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\ResourceModel;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CustomerRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\Data\CustomerSecureFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSecureFactory;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRegistry;

    /**
     * @var \Magento\Customer\Model\ResourceModel\AddressRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRepository;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerResourceModel;

    /**
     * @var \Magento\Customer\Api\CustomerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMetadata;

    /**
     * @var \Magento\Customer\Api\Data\CustomerSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Api\ImageProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $imageProcessor;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customer;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    protected $model;

    protected function setUp()
    {
        $this->customerResourceModel =
            $this->createMock(\Magento\Customer\Model\ResourceModel\Customer::class);
        $this->customerRegistry = $this->createMock(\Magento\Customer\Model\CustomerRegistry::class);
        $this->dataObjectHelper = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);
        $this->customerFactory  =
            $this->createPartialMock(\Magento\Customer\Model\CustomerFactory::class, ['create']);
        $this->customerSecureFactory = $this->createPartialMock(
            \Magento\Customer\Model\Data\CustomerSecureFactory::class,
            ['create']
        );
        $this->addressRepository = $this->createMock(\Magento\Customer\Model\ResourceModel\AddressRepository::class);
        $this->customerMetadata = $this->getMockForAbstractClass(
            \Magento\Customer\Api\CustomerMetadataInterface::class,
            [],
            '',
            false
        );
        $this->searchResultsFactory = $this->createPartialMock(
            \Magento\Customer\Api\Data\CustomerSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->eventManager = $this->getMockForAbstractClass(
            \Magento\Framework\Event\ManagerInterface::class,
            [],
            '',
            false
        );
        $this->storeManager = $this->getMockForAbstractClass(
            \Magento\Store\Model\StoreManagerInterface::class,
            [],
            '',
            false
        );
        $this->extensibleDataObjectConverter = $this->createMock(
            \Magento\Framework\Api\ExtensibleDataObjectConverter::class
        );
        $this->imageProcessor = $this->getMockForAbstractClass(
            \Magento\Framework\Api\ImageProcessorInterface::class,
            [],
            '',
            false
        );
        $this->extensionAttributesJoinProcessor = $this->getMockForAbstractClass(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class,
            [],
            '',
            false
        );
        $this->customer = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerInterface::class,
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
        $this->model = new \Magento\Customer\Model\ResourceModel\CustomerRepository(
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
            $this->collectionProcessorMock
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSave()
    {
        $customerId = 1;
        $storeId = 2;

        $region = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\RegionInterface::class, [], '', false);
        $address = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AddressInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'setCustomerId',
                'setRegion',
                'getRegion',
                'getId'
            ]
        );
        $address2 = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AddressInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'setCustomerId',
                'setRegion',
                'getRegion',
                'getId'
            ]
        );
        $customerModel = $this->createPartialMock(\Magento\Customer\Model\Customer::class, [
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
                'setFailuresNum',
                'setFirstFailure',
                'setLockExpires',
                'save',
            ]);

        $this->customer->expects($this->atLeastOnce())
            ->method('__toArray')
            ->willReturn(['default_billing', 'default_shipping']);

        $customerAttributesMetaData = $this->getMockForAbstractClass(
            \Magento\Framework\Api\CustomAttributesDataInterface::class,
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
        $customerSecureData = $this->createPartialMock(\Magento\Customer\Model\Data\CustomerSecure::class, [
                'getRpToken',
                'getRpTokenCreatedAt',
                'getPasswordHash',
                'getFailuresNum',
                'getFirstFailure',
                'getLockExpires',
            ]);
        $this->customer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
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
        $address->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId)
            ->willReturnSelf();
        $address->expects($this->once())
            ->method('getRegion')
            ->willReturn($region);
        $address->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(7);
        $address->expects($this->once())
            ->method('setRegion')
            ->with($region);
        $customerAttributesMetaData->expects($this->atLeastOnce())
            ->method('getAddresses')
            ->willReturn([$address]);
        $customerAttributesMetaData->expects($this->at(1))
            ->method('setAddresses')
            ->with([]);
        $customerAttributesMetaData->expects($this->at(2))
            ->method('setAddresses')
            ->with([$address]);
        $this->extensibleDataObjectConverter->expects($this->once())
            ->method('toNestedArray')
            ->with($customerAttributesMetaData, [], \Magento\Customer\Api\Data\CustomerInterface::class)
            ->willReturn(['customerData']);
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->with(['data' => ['customerData']])
            ->willReturn($customerModel);
        $customerModel->expects($this->once())
            ->method('getStoreId')
            ->willReturn(null);
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManager
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($store);
        $customerModel->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $customerModel->expects($this->once())
            ->method('setId')
            ->with($customerId);
        $customerModel->expects($this->once())
            ->method('getAttributeSetId')
            ->willReturn(null);
        $customerModel->expects($this->once())
            ->method('setAttributeSetId')
            ->with(\Magento\Customer\Api\CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER);
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
            ->willReturnMap([
                ['rpToken', $customerModel],
                [null, $customerModel],
            ]);
        $customerModel->expects($this->once())
            ->method('setRpTokenCreatedAt')
            ->willReturnMap([
                ['rpTokenCreatedAt', $customerModel],
                [null, $customerModel],
            ]);

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
        $this->customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address, $address2]);
        $this->addressRepository->expects($this->once())
            ->method('save')
            ->with($address);
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
                ['customer_data_object' => $this->customer, 'orig_customer_data_object' => $customerAttributesMetaData]
            );

        $this->model->save($this->customer);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSaveWithPasswordHash()
    {
        $customerId = 1;
        $storeId = 2;
        $passwordHash = 'ukfa4sdfa56s5df02asdf4rt';

        $customerSecureData = $this->createPartialMock(\Magento\Customer\Model\Data\CustomerSecure::class, [
                'getRpToken',
                'getRpTokenCreatedAt',
                'getPasswordHash',
                'getFailuresNum',
                'getFirstFailure',
                'getLockExpires',
            ]);
        $region = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\RegionInterface::class,
            [],
            '',
            false
        );
        $address = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AddressInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'setCustomerId',
                'setRegion',
                'getRegion',
                'getId'
            ]
        );
        $address2 = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AddressInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'setCustomerId',
                'setRegion',
                'getRegion',
                'getId'
            ]
        );

        $this->customer->expects($this->atLeastOnce())
            ->method('__toArray')
            ->willReturn(['default_billing', 'default_shipping']);

        $customerModel = $this->createPartialMock(\Magento\Customer\Model\Customer::class, [
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
                'save',
            ]);
        $customerAttributesMetaData = $this->getMockForAbstractClass(
            \Magento\Framework\Api\CustomAttributesDataInterface::class,
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
        $address->expects($this->once())
            ->method('setCustomerId')
            ->with($customerId)
            ->willReturnSelf();
        $address->expects($this->once())
            ->method('getRegion')
            ->willReturn($region);
        $address->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(7);
        $address->expects($this->once())
            ->method('setRegion')
            ->with($region);
        $customerAttributesMetaData->expects($this->any())
            ->method('getAddresses')
            ->willReturn([$address]);
        $customerAttributesMetaData->expects($this->at(1))
            ->method('setAddresses')
            ->with([]);
        $customerAttributesMetaData->expects($this->at(2))
            ->method('setAddresses')
            ->with([$address]);
        $customerAttributesMetaData
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $this->extensibleDataObjectConverter->expects($this->once())
            ->method('toNestedArray')
            ->with($customerAttributesMetaData, [], \Magento\Customer\Api\Data\CustomerInterface::class)
            ->willReturn(['customerData']);
        $this->customerFactory->expects($this->once())
            ->method('create')
            ->with(['data' => ['customerData']])
            ->willReturn($customerModel);
        $customerModel->expects($this->once())
            ->method('getStoreId')
            ->willReturn(null);
        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManager
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($store);
        $customerModel->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $customerModel->expects($this->once())
            ->method('setId')
            ->with($customerId);
        $customerModel->expects($this->once())
            ->method('getAttributeSetId')
            ->willReturn(null);
        $customerModel->expects($this->once())
            ->method('setAttributeSetId')
            ->with(\Magento\Customer\Api\CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER);
        $customerModel->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($customerId);
        $customerModel->expects($this->once())
            ->method('save');
        $this->customerRegistry->expects($this->once())
            ->method('push')
            ->with($customerModel);
        $this->customer->expects($this->any())
            ->method('getAddresses')
            ->willReturn([$address, $address2]);
        $this->addressRepository->expects($this->once())
            ->method('save')
            ->with($address);
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
                ['customer_data_object' => $this->customer, 'orig_customer_data_object' => $customerAttributesMetaData]
            );

        $this->model->save($this->customer, $passwordHash);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetList()
    {
        $collection = $this->createMock(\Magento\Customer\Model\ResourceModel\Customer\Collection::class);
        $searchResults = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AddressSearchResultsInterface::class,
            [],
            '',
            false
        );
        $searchCriteria = $this->getMockForAbstractClass(
            \Magento\Framework\Api\SearchCriteriaInterface::class,
            [],
            '',
            false
        );
        $customerModel = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
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
            \Magento\Customer\Api\Data\AttributeMetadataInterface::class,
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
            ->with($collection, \Magento\Customer\Api\Data\CustomerInterface::class);
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
            ->with('company', 'customer_address/company', 'default_billing', null, 'left')
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
            ->willReturn($this->customer);
        $searchResults->expects($this->once())
            ->method('setItems')
            ->with([$this->customer]);

        $this->assertSame($searchResults, $this->model->getList($searchCriteria));
    }

    public function testDeleteById()
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

    public function testDelete()
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

        $this->assertTrue($this->model->delete($this->customer));
    }
}
