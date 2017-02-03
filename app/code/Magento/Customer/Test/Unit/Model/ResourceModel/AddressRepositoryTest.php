<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\ResourceModel;

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

    public function setUp()
    {
        $this->addressFactory = $this->getMock('Magento\Customer\Model\AddressFactory', ['create'], [], '', false);
        $this->addressRegistry = $this->getMock('Magento\Customer\Model\AddressRegistry', [], [], '', false);
        $this->customerRegistry = $this->getMock('Magento\Customer\Model\CustomerRegistry', [], [], '', false);
        $this->addressResourceModel = $this->getMock('Magento\Customer\Model\ResourceModel\Address', [], [], '', false);
        $this->directoryData = $this->getMock('Magento\Directory\Helper\Data', [], [], '', false);
        $this->addressSearchResultsFactory = $this->getMock(
            'Magento\Customer\Api\Data\AddressSearchResultsInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->addressCollectionFactory = $this->getMock(
            'Magento\Customer\Model\ResourceModel\Address\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->extensionAttributesJoinProcessor = $this->getMockForAbstractClass(
            'Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface',
            [],
            '',
            false
        );
        $this->customer = $this->getMock('Magento\Customer\Model\Customer', [], [], '', false);
        $this->address = $this->getMock(
            'Magento\Customer\Model\Address',
            [
                'getId',
                'getCountryId',
                'getFirstname',
                'getLastname',
                'getStreetLine',
                'getCity',
                'getTelephone',
                'getRegionId',
                'updateData',
                'setCustomer',
                'getCountryModel',
                'getShouldIgnoreValidation',
                'save',
                'getDataModel',
                'getCustomerId',
            ],
            [],
            '',
            false
        );

        $this->repository = new \Magento\Customer\Model\ResourceModel\AddressRepository(
            $this->addressFactory,
            $this->addressRegistry,
            $this->customerRegistry,
            $this->addressResourceModel,
            $this->directoryData,
            $this->addressSearchResultsFactory,
            $this->addressCollectionFactory,
            $this->extensionAttributesJoinProcessor
        );
    }

    public function testSave()
    {
        $customerId = 34;
        $addressId = 53;
        $customerAddress = $this->getMockForAbstractClass('Magento\Customer\Api\Data\AddressInterface', [], '', false);
        $addressCollection =
            $this->getMock('Magento\Customer\Model\ResourceModel\Address\Collection', [], [], '', false);
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
            ->method('save');
        $this->customerRegistry
            ->expects($this->once())
            ->method('remove')
            ->with($customerId);
        $this->addressRegistry->expects($this->once())
            ->method('push')
            ->with($this->address);
        $this->customer->expects($this->once())
            ->method('getAddressesCollection')
            ->willReturn($addressCollection);
        $addressCollection->expects($this->once())
            ->method('clear');
        $this->address->expects($this->once())
            ->method('getDataModel')
            ->willReturn($customerAddress);
        $this->address->expects($this->once())
            ->method('getShouldIgnoreValidation')
            ->willReturn(true);

        $this->repository->save($customerAddress);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testSaveWithException()
    {
        $customerId = 34;
        $addressId = 53;
        $customerAddress = $this->getMockForAbstractClass('Magento\Customer\Api\Data\AddressInterface', [], '', false);
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
        $this->prepareMocksForInvalidAddressValidation();

        $this->repository->save($customerAddress);
    }

    protected function prepareMocksForInvalidAddressValidation()
    {
        $countryModel = $this->getMock('Magento\Directory\Model\Country', [], [], '', false);
        $regionCollection = $this->getMock(
            'Magento\Directory\Model\ResourceModel\Region\Collection',
            [],
            [],
            '',
            false
        );

        $this->address->expects($this->once())
            ->method('getShouldIgnoreValidation')
            ->willReturn(false);
        $this->address->expects($this->atLeastOnce())
            ->method('getCountryId');
        $this->address->expects($this->once())
            ->method('getFirstname');
        $this->address->expects($this->once())
            ->method('getLastname');
        $this->address->expects($this->once())
            ->method('getStreetLine')
            ->with(1);
        $this->address->expects($this->once())
            ->method('getCity');
        $this->address->expects($this->once())
            ->method('getTelephone');
        $this->address->expects($this->once())
            ->method('getRegionId')
            ->willReturn(null);

        $this->directoryData->expects($this->once())
            ->method('getCountriesWithOptionalZip')
            ->willReturn([]);
        $this->address->expects($this->once())
            ->method('getCountryModel')
            ->willReturn($countryModel);
        $countryModel->expects($this->once())
            ->method('getRegionCollection')
            ->willReturn($regionCollection);
        $regionCollection->expects($this->once())
            ->method('getSize')
            ->willReturn(2);
        $this->directoryData->expects($this->once())
            ->method('isRegionRequired')
            ->with(null)
            ->willReturn(true);
    }

    public function testGetById()
    {
        $customerAddress = $this->getMockForAbstractClass('Magento\Customer\Api\Data\AddressInterface', [], '', false);
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
        $filterGroup = $this->getMock('Magento\Framework\Api\Search\FilterGroup', [], [], '', false);
        $filter = $this->getMock('Magento\Framework\Api\Filter', [], [], '', false);
        $collection = $this->getMock('Magento\Customer\Model\ResourceModel\Address\Collection', [], [], '', false);
        $searchResults = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\AddressSearchResultsInterface',
            [],
            '',
            false
        );
        $searchCriteria = $this->getMockForAbstractClass(
            'Magento\Framework\Api\SearchCriteriaInterface',
            [],
            '',
            false
        );
        $this->addressSearchResultsFactory->expects($this->once())
            ->method('create')
            ->willReturn($searchResults);
        $this->addressCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);
        $this->extensionAttributesJoinProcessor->expects($this->once())
            ->method('process')
            ->with($collection, 'Magento\Customer\Api\Data\AddressInterface');
        $searchCriteria->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroup]);
        $filterGroup->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filter]);
        $filter->expects($this->once())
            ->method('getConditionType')
            ->willReturn(false);
        $filter->expects($this->once())
            ->method('getField')
            ->willReturn('Field');
        $filter->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn('Value');
        $collection->expects($this->once())
            ->method('addFieldToFilter')
            ->with([['attribute' => 'Field', 'eq' => 'Value']], [['eq' => 'Value']]);
        $collection->expects($this->once())
            ->method('getSize')
            ->willReturn(23);
        $searchResults->expects($this->once())
            ->method('setTotalCount')
            ->with(23);
        $sortOrder = $this->getMock('Magento\Framework\Api\SortOrder', [], [], '', false);
        $searchCriteria->expects($this->once())
            ->method('getSortOrders')
            ->willReturn([$sortOrder]);
        $sortOrder->expects($this->once())
            ->method('getField')
            ->willReturn('Field');
        $sortOrder->expects($this->once())
            ->method('getDirection')
            ->willReturn(\Magento\Framework\Api\SortOrder::SORT_ASC);
        $collection->expects($this->once())
            ->method('addOrder')
            ->with('Field', 'ASC');
        $searchCriteria->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn(1);
        $collection->expects($this->once())
            ->method('setCurPage')
            ->with(1);
        $searchCriteria->expects($this->once())
            ->method('getPageSize')
            ->willReturn(10);
        $collection->expects($this->once())
            ->method('setPageSize')
            ->with(10);
        $collection->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->address]);
        $this->address->expects($this->once())
            ->method('getId')
            ->willReturn(12);
        $customerAddress = $this->getMockForAbstractClass('Magento\Customer\Api\Data\AddressInterface', [], '', false);
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
            'Magento\Customer\Model\ResourceModel\Address\Collection',
            [],
            [],
            '',
            false
        );
        $customerAddress = $this->getMockForAbstractClass('Magento\Customer\Api\Data\AddressInterface', [], '', false);
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
            'Magento\Customer\Model\ResourceModel\Address\Collection',
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
            ->method('clear');
        $this->addressResourceModel->expects($this->once())
            ->method('delete')
            ->with($this->address);
        $this->addressRegistry->expects($this->once())
            ->method('remove')
            ->with($addressId);

        $this->assertTrue($this->repository->deleteById($addressId));
    }
}
