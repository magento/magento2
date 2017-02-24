<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\ResourceModel;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Directory\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryData;

    /**
     * @var \Magento\Customer\Model\AddressFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressFactory;

    /**
     * @var \Magento\Customer\Model\AddressRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRegistry;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRegistry;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressResourceModel;

    /**
     * @var \Magento\Customer\Api\Data\AddressSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressSearchResultsFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressCollectionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\Customer\Model\Customer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customer;

    /**
     * @var \Magento\Customer\Model\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $address;

    /**
     * @var \Magento\Customer\Model\ResourceModel\AddressRepository
     */
    protected $repository;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    protected function setUp()
    {
        $this->addressFactory = $this->getMock(
            \Magento\Customer\Model\AddressFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->addressRegistry = $this->getMock(\Magento\Customer\Model\AddressRegistry::class, [], [], '', false);
        $this->customerRegistry = $this->getMock(\Magento\Customer\Model\CustomerRegistry::class, [], [], '', false);
        $this->addressResourceModel = $this->getMock(
            \Magento\Customer\Model\ResourceModel\Address::class,
            [],
            [],
            '',
            false
        );
        $this->directoryData = $this->getMock(\Magento\Directory\Helper\Data::class, [], [], '', false);
        $this->addressSearchResultsFactory = $this->getMock(
            \Magento\Customer\Api\Data\AddressSearchResultsInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->addressCollectionFactory = $this->getMock(
            \Magento\Customer\Model\ResourceModel\Address\CollectionFactory::class,
            ['create'],
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
        $this->customer = $this->getMock(\Magento\Customer\Model\Customer::class, [], [], '', false);
        $this->address = $this->getMock(
            \Magento\Customer\Model\Address::class,
            [
                'getId',
                'getCountryId',
                'getFirstname',
                'getLastname',
                'getStreetLine',
                'getCity',
                'getTelephone',
                'getRegionId',
                'getRegion',
                'updateData',
                'setCustomer',
                'getCountryModel',
                'getShouldIgnoreValidation',
                'validate',
                'save',
                'getDataModel',
                'getCustomerId',
            ],
            [],
            '',
            false
        );

        $this->collectionProcessor = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMockForAbstractClass();

        $this->repository = new \Magento\Customer\Model\ResourceModel\AddressRepository(
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
            \Magento\Customer\Api\Data\AddressInterface::class,
            [],
            '',
            false
        );
        $addressCollection =
            $this->getMock(\Magento\Customer\Model\ResourceModel\Address\Collection::class, [], [], '', false);
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

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testSaveWithException()
    {
        $customerId = 34;
        $addressId = 53;
        $errors[] = __('Please enter the state/province.');
        $customerAddress = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AddressInterface::class,
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

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage region is a required field.
     */
    public function testSaveWithInvalidRegion()
    {
        $customerId = 34;
        $addressId = 53;
        $errors[] = __('region is a required field.');
        $customerAddress = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AddressInterface::class,
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

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage regionId is a required field.
     */
    public function testSaveWithInvalidRegionId()
    {
        $customerId = 34;
        $addressId = 53;
        $errors[] = __('regionId is a required field.');
        $customerAddress = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AddressInterface::class,
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
            \Magento\Customer\Api\Data\AddressInterface::class,
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
        $collection = $this->getMock(
            \Magento\Customer\Model\ResourceModel\Address\Collection::class,
            [],
            [],
            '',
            false
        );
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
        $this->addressSearchResultsFactory->expects($this->once())->method('create')->willReturn($searchResults);
        $this->addressCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->extensionAttributesJoinProcessor->expects($this->once())
            ->method('process')
            ->with($collection, \Magento\Customer\Api\Data\AddressInterface::class);

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
            \Magento\Customer\Api\Data\AddressInterface::class,
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

        $addressCollection = $this->getMock(
            \Magento\Customer\Model\ResourceModel\Address\Collection::class,
            [],
            [],
            '',
            false
        );
        $customerAddress = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\AddressInterface::class,
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
        $addressCollection = $this->getMock(
            \Magento\Customer\Model\ResourceModel\Address\Collection::class,
            [],
            [],
            '',
            false
        );
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
