<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ResourceModel;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressSearchResultsInterface;
use Magento\Customer\Api\Data\AddressSearchResultsInterfaceFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Address;
use Magento\Customer\Model\ResourceModel\Address\Collection;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;
use Magento\Customer\Model\ResourceModel\AddressRepository;
use Magento\Directory\Helper\Data;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressRepositoryTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    protected $directoryData;

    /**
     * @var AddressFactory|MockObject
     */
    protected $addressFactory;

    /**
     * @var AddressRegistry|MockObject
     */
    protected $addressRegistry;

    /**
     * @var CustomerRegistry|MockObject
     */
    protected $customerRegistry;

    /**
     * @var Address|MockObject
     */
    protected $addressResourceModel;

    /**
     * @var AddressSearchResultsInterfaceFactory|MockObject
     */
    protected $addressSearchResultsFactory;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $addressCollectionFactory;

    /**
     * @var JoinProcessorInterface|MockObject
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var Customer|MockObject
     */
    protected $customer;

    /**
     * @var \Magento\Customer\Model\Address|MockObject
     */
    protected $address;

    /**
     * @var AddressRepository
     */
    protected $repository;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessor;

    protected function setUp(): void
    {
        $this->addressFactory = $this->createPartialMock(AddressFactory::class, ['create']);
        $this->addressRegistry = $this->createMock(AddressRegistry::class);
        $this->customerRegistry = $this->createMock(CustomerRegistry::class);
        $this->addressResourceModel = $this->createMock(Address::class);
        $this->directoryData = $this->createMock(Data::class);
        $this->addressSearchResultsFactory = $this->createPartialMock(
            AddressSearchResultsInterfaceFactory::class,
            ['create']
        );
        $this->addressCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->extensionAttributesJoinProcessor = $this->getMockForAbstractClass(
            JoinProcessorInterface::class,
            [],
            '',
            false
        );
        $this->customer = $this->createMock(Customer::class);
        $this->address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)->addMethods(
            ['getCountryId', 'getFirstname', 'getLastname', 'getCity', 'getTelephone', 'getShouldIgnoreValidation']
        )
            ->onlyMethods(
                [
                    'getId',
                    'getStreetLine',
                    'getRegionId',
                    'getRegion',
                    'updateData',
                    'setCustomer',
                    'getCountryModel',
                    'validate',
                    'save',
                    'getDataModel',
                    'getCustomerId'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMockForAbstractClass();

        $this->repository = new AddressRepository(
            $this->addressFactory,
            $this->addressRegistry,
            $this->customerRegistry,
            $this->addressResourceModel,
            $this->directoryData,
            $this->addressSearchResultsFactory,
            $this->addressCollectionFactory,
            $this->extensionAttributesJoinProcessor,
            $this->collectionProcessor
        );
    }

    public function testSave()
    {
        $customerId = 34;
        $addressId = 53;
        $customerAddress = $this->getMockForAbstractClass(
            AddressInterface::class,
            [],
            '',
            false
        );
        $addressCollection =
            $this->createMock(Collection::class);
        $customerAddress->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $customerAddress->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($addressId);
        $this->customerRegistry->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($this->customer);
        $this->address->expects($this->atLeastOnce())
            ->method("getId")
            ->willReturn($addressId);
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with($addressId)
            ->willReturn(null);
        $this->addressFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->address);
        $this->address->expects($this->once())
            ->method('updateData')
            ->with($customerAddress);
        $this->address->expects($this->once())
            ->method('setCustomer')
            ->with($this->customer);
        $this->address->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $this->address->expects($this->once())
            ->method('save');
        $this->addressRegistry->expects($this->once())
            ->method('push')
            ->with($this->address);
        $this->customer->expects($this->exactly(2))
            ->method('getAddressesCollection')
            ->willReturn($addressCollection);
        $addressCollection->expects($this->once())
            ->method("removeItemByKey")
            ->with($addressId);
        $addressCollection->expects($this->once())
            ->method("addItem")
            ->with($this->address);
        $this->address->expects($this->once())
            ->method('getDataModel')
            ->willReturn($customerAddress);

        $this->repository->save($customerAddress);
    }

    public function testSaveWithException()
    {
        $this->expectException(InputException::class);

        $customerId = 34;
        $addressId = 53;
        $errors[] = __('Please enter the state/province.');
        $customerAddress = $this->getMockForAbstractClass(
            AddressInterface::class,
            [],
            '',
            false
        );
        $customerAddress->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $customerAddress->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($addressId);
        $this->customerRegistry->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($this->customer);
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with($addressId)
            ->willReturn($this->address);
        $this->address->expects($this->once())
            ->method('updateData')
            ->with($customerAddress);
        $this->address->expects($this->once())
            ->method('validate')
            ->willReturn($errors);

        $this->repository->save($customerAddress);
    }

    public function testSaveWithInvalidRegion()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('region is a required field.');

        $customerId = 34;
        $addressId = 53;
        $errors[] = __('region is a required field.');
        $customerAddress = $this->getMockForAbstractClass(
            AddressInterface::class,
            [],
            '',
            false
        );
        $customerAddress->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $customerAddress->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($addressId);
        $this->customerRegistry->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($this->customer);
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with($addressId)
            ->willReturn($this->address);
        $this->address->expects($this->once())
            ->method('updateData')
            ->with($customerAddress);

        $this->address->expects($this->never())
            ->method('getRegionId')
            ->willReturn(null);
        $this->address->expects($this->once())
            ->method('validate')
            ->willReturn($errors);

        $this->repository->save($customerAddress);
    }

    public function testSaveWithInvalidRegionId()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('"regionId" is required. Enter and try again.');

        $customerId = 34;
        $addressId = 53;
        $errors[] = __('"regionId" is required. Enter and try again.');
        $customerAddress = $this->getMockForAbstractClass(
            AddressInterface::class,
            [],
            '',
            false
        );
        $customerAddress->expects($this->atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $customerAddress->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($addressId);
        $this->customerRegistry->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($this->customer);
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with($addressId)
            ->willReturn($this->address);
        $this->address->expects($this->once())
            ->method('updateData')
            ->with($customerAddress);
        $this->address->expects($this->never())
            ->method('getRegion')
            ->willReturn('');
        $this->address->expects($this->once())
            ->method('validate')
            ->willReturn($errors);

        $this->repository->save($customerAddress);
    }

    public function testGetById()
    {
        $customerAddress = $this->getMockForAbstractClass(
            AddressInterface::class,
            [],
            '',
            false
        );
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with(12)
            ->willReturn($this->address);
        $this->address->expects($this->once())
            ->method('getDataModel')
            ->willReturn($customerAddress);

        $this->assertSame($customerAddress, $this->repository->getById(12));
    }

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
        $this->addressSearchResultsFactory->expects($this->once())->method('create')->willReturn($searchResults);
        $this->addressCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->extensionAttributesJoinProcessor->expects($this->once())
            ->method('process')
            ->with($collection, AddressInterface::class);

        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection)
            ->willReturnSelf();

        $collection->expects($this->once())->method('getSize')->willReturn(23);
        $searchResults->expects($this->once())
            ->method('setTotalCount')
            ->with(23);
        $collection->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->address]);
        $this->address->expects($this->once())
            ->method('getId')
            ->willReturn(12);
        $customerAddress = $this->getMockForAbstractClass(
            AddressInterface::class,
            [],
            '',
            false
        );
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with(12)
            ->willReturn($this->address);
        $this->address->expects($this->once())
            ->method('getDataModel')
            ->willReturn($customerAddress);
        $searchResults->expects($this->once())
            ->method('setItems')
            ->with([$customerAddress]);
        $searchResults->expects($this->once())
            ->method('setSearchCriteria')
            ->with($searchCriteria);

        $this->assertSame($searchResults, $this->repository->getList($searchCriteria));
    }

    public function testDelete()
    {
        $addressId = 12;
        $customerId = 43;

        $addressCollection = $this->createMock(Collection::class);
        $customerAddress = $this->getMockForAbstractClass(
            AddressInterface::class,
            [],
            '',
            false
        );
        $customerAddress->expects($this->once())
            ->method('getId')
            ->willReturn($addressId);
        $this->address->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with($addressId)
            ->willReturn($this->address);
        $this->customerRegistry->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($this->customer);

        $this->customer->expects($this->once())
            ->method('getAddressesCollection')
            ->willReturn($addressCollection);
        $addressCollection->expects($this->once())
            ->method('clear');
        $this->addressResourceModel->expects($this->once())
            ->method('delete')
            ->with($this->address);
        $this->addressRegistry->expects($this->once())
            ->method('remove')
            ->with($addressId);

        $this->assertTrue($this->repository->delete($customerAddress));
    }

    public function testDeleteById()
    {
        $addressId = 12;
        $customerId = 43;

        $this->address->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $addressCollection = $this->createMock(Collection::class);
        $this->addressRegistry->expects($this->once())
            ->method('retrieve')
            ->with($addressId)
            ->willReturn($this->address);
        $this->customerRegistry->expects($this->once())
            ->method('retrieve')
            ->with($customerId)
            ->willReturn($this->customer);
        $this->customer->expects($this->once())
            ->method('getAddressesCollection')
            ->willReturn($addressCollection);
        $addressCollection->expects($this->once())
            ->method('removeItemByKey')
            ->with($addressId);
        $this->addressResourceModel->expects($this->once())
            ->method('delete')
            ->with($this->address);
        $this->addressRegistry->expects($this->once())
            ->method('remove')
            ->with($addressId);

        $this->assertTrue($this->repository->deleteById($addressId));
    }
}
