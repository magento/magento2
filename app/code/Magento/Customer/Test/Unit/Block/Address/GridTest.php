<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Block\Address\Grid;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\ResourceModel\Address\Collection;
use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Theme\Block\Html\Pager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * Unit tests for \Magento\Customer\Block\Address\Grid class
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GridTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CurrentCustomer|MockObject
     */
    private $addressCollectionFactory;

    /**
     * @var CollectionFactory|MockObject
     */
    private $currentCustomer;

    /**
     * @var CountryFactory|MockObject
     */
    private $countryFactory;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilder;

    /**
     * @var Grid
     */
    private $gridBlock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->currentCustomer = $this->getMockBuilder(CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCustomer'])
            ->getMock();

        $this->addressCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->countryFactory = $this->getMockBuilder(CountryFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->urlBuilder = $this->createMock(UrlInterface::class);

        $this->gridBlock = $this->objectManager->getObject(
            Grid::class,
            [
                'addressCollectionFactory' => $this->addressCollectionFactory,
                'currentCustomer' => $this->currentCustomer,
                'countryFactory' => $this->countryFactory,
                '_urlBuilder' => $this->urlBuilder
            ]
        );
    }

    /**
     * Test for \Magento\Customer\Block\Address\Book::getChildHtml method with 'pager' argument
     */
    public function testGetChildHtml()
    {
        $customerId = 1;
        $outputString = 'OutputString';
        /** @var Pager|MockObject $block */
        $block = $this->createPartialMock(Pager::class, ['setCollection']);
        /** @var LayoutInterface|MockObject $layout */
        $layout = $this->createMock(LayoutInterface::class);
        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->createMock(CustomerInterface::class);
        /** @var MockObject */
        $addressCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setOrder', 'setCustomerFilter', 'load','addFieldToFilter'])
            ->getMock();

        $layout->expects($this->atLeastOnce())->method('getChildName')->with('NameInLayout', 'pager')
            ->willReturn('ChildName');
        $layout->expects($this->atLeastOnce())->method('renderElement')->with('ChildName', true)
            ->willReturn('OutputString');
        $layout->expects($this->atLeastOnce())->method('createBlock')
            ->with(Pager::class, 'customer.addresses.pager')->willReturn($block);
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $this->currentCustomer->expects($this->atLeastOnce())->method('getCustomer')->willReturn($customer);
        $addressCollection->expects($this->atLeastOnce())->method('setOrder')->with('entity_id', 'desc')
            ->willReturnSelf();
        $addressCollection->expects($this->atLeastOnce())->method('setCustomerFilter')->with([$customerId])
            ->willReturnSelf();
        $addressCollection->expects(static::any())->method('addFieldToFilter')->willReturnSelf();
        $this->addressCollectionFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($addressCollection);
        $block->expects($this->atLeastOnce())->method('setCollection')->with($addressCollection)->willReturnSelf();
        $this->gridBlock->setNameInLayout('NameInLayout');
        $this->gridBlock->setLayout($layout);
        $this->assertEquals($outputString, $this->gridBlock->getChildHtml('pager'));
    }

    /**
     * Test for \Magento\Customer\Block\Address\Grid::getAddressEditUrl method
     */
    public function testGetAddAddressUrl()
    {
        $addressId = 1;
        $expectedUrl = 'expected_url';
        $this->urlBuilder->expects($this->atLeastOnce())->method('getUrl')
            ->with('customer/address/edit', ['_secure' => true, 'id' => $addressId])
            ->willReturn($expectedUrl);
        $this->assertEquals($expectedUrl, $this->gridBlock->getAddressEditUrl($addressId));
    }

    public function testGetAdditionalAddresses()
    {
        $customerId = 1;
        /** @var CustomerInterface|MockObject $customer */
        $customer = $this->createMock(CustomerInterface::class);
        /** @var MockObject */
        $addressCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setOrder', 'setCustomerFilter', 'load', 'getIterator','addFieldToFilter'])
            ->getMock();
        $addressDataModel = $this->createMock(AddressInterface::class);
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getDataModel'])
            ->getMock();
        $collection = [$address, $address, $address];
        $address->expects($this->exactly(3))->method('getId')
            ->willReturnOnConsecutiveCalls(1, 2, 3);
        $address->expects($this->atLeastOnce())->method('getDataModel')->willReturn($addressDataModel);
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $customer->expects($this->atLeastOnce())->method('getDefaultBilling')->willReturn('1');
        $customer->expects($this->atLeastOnce())->method('getDefaultShipping')->willReturn('2');

        $this->currentCustomer->expects($this->atLeastOnce())->method('getCustomer')->willReturn($customer);
        $addressCollection->expects($this->atLeastOnce())->method('setOrder')->with('entity_id', 'desc')
            ->willReturnSelf();
        $addressCollection->expects($this->atLeastOnce())->method('setCustomerFilter')->with([$customerId])
            ->willReturnSelf();
        $addressCollection->expects(static::any())->method('addFieldToFilter')->willReturnSelf();
        $addressCollection->expects($this->atLeastOnce())->method('getIterator')
            ->willReturn(new \ArrayIterator($collection));
        $this->addressCollectionFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($addressCollection);

        $this->assertEquals($addressDataModel, $this->gridBlock->getAdditionalAddresses()[0]);
    }

    /**
     * Test for \Magento\Customer\ViewModel\CustomerAddress::getStreetAddress method
     */
    public function testGetStreetAddress()
    {
        $street = ['Line 1', 'Line 2'];
        $expectedAddress = 'Line 1, Line 2';
        $address = $this->createMock(AddressInterface::class);
        $address->expects($this->atLeastOnce())->method('getStreet')->willReturn($street);
        $this->assertEquals($expectedAddress, $this->gridBlock->getStreetAddress($address));
    }

    /**
     * Test for \Magento\Customer\ViewModel\CustomerAddress::getCountryByCode method
     */
    public function testGetCountryByCode()
    {
        $countryId = 'US';
        $countryName = 'United States';
        $country = $this->getMockBuilder(Country::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadByCode', 'getName'])
            ->getMock();
        $this->countryFactory->expects($this->atLeastOnce())->method('create')->willReturn($country);
        $country->expects($this->atLeastOnce())->method('loadByCode')->with($countryId)->willReturnSelf();
        $country->expects($this->atLeastOnce())->method('getName')->willReturn($countryName);
        $this->assertEquals($countryName, $this->gridBlock->getCountryByCode($countryId));
    }
}
