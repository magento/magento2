<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Address;

use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;

/**
 * Unit tests for \Magento\Customer\Block\Address\Grid class
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GridTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currentCustomer;

    /**
     * @var \Magento\Directory\Model\CountryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $countryFactory;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Customer\Block\Address\Grid
     */
    private $gridBlock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->currentCustomer = $this->getMockBuilder(\Magento\Customer\Helper\Session\CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomer'])
            ->getMock();

        $this->addressCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->countryFactory = $this->getMockBuilder(\Magento\Directory\Model\CountryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);

        $this->gridBlock = $this->objectManager->getObject(
            \Magento\Customer\Block\Address\Grid::class,
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

        /** @var \Magento\Framework\View\Element\BlockInterface|\PHPUnit_Framework_MockObject_MockObject $block */
        $block = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)
            ->setMethods(['setCollection'])
            ->getMockForAbstractClass();
        /** @var  $layout \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject */
        $layout = $this->getMockForAbstractClass(\Magento\Framework\View\LayoutInterface::class);
        /** @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject $customer */
        $customer = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\CustomerInterface::class);
        /** @var \PHPUnit_Framework_MockObject_MockObject */
        $addressCollection = $this->getMockBuilder(\Magento\Customer\Model\ResourceModel\Address\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOrder', 'setCustomerFilter', 'load'])
            ->getMock();

        $layout->expects($this->atLeastOnce())->method('getChildName')->with('NameInLayout', 'pager')
            ->willReturn('ChildName');
        $layout->expects($this->atLeastOnce())->method('renderElement')->with('ChildName', true)
            ->willReturn('OutputString');
        $layout->expects($this->atLeastOnce())->method('createBlock')
            ->with(\Magento\Theme\Block\Html\Pager::class, 'customer.addresses.pager')->willReturn($block);
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $this->currentCustomer->expects($this->atLeastOnce())->method('getCustomer')->willReturn($customer);
        $addressCollection->expects($this->atLeastOnce())->method('setOrder')->with('entity_id', 'desc')
            ->willReturnSelf();
        $addressCollection->expects($this->atLeastOnce())->method('setCustomerFilter')->with([$customerId])
            ->willReturnSelf();
        $this->addressCollectionFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($addressCollection);
        $block->expects($this->atLeastOnce())->method('setCollection')->with($addressCollection)->willReturnSelf();
        $this->gridBlock->setNameInLayout('NameInLayout');
        $this->gridBlock->setLayout($layout);
        $this->assertEquals('OutputString', $this->gridBlock->getChildHtml('pager'));
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
        /** @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject $customer */
        $customer = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\CustomerInterface::class);
        /** @var \PHPUnit_Framework_MockObject_MockObject */
        $addressCollection = $this->getMockBuilder(\Magento\Customer\Model\ResourceModel\Address\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOrder', 'setCustomerFilter', 'load', 'getIterator'])
            ->getMock();
        $addressDataModel = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\AddressInterface::class);
        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getDataModel'])
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
        $address = $this->getMockForAbstractClass(\Magento\Customer\Api\Data\AddressInterface::class);
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
        $country = $this->getMockBuilder(\Magento\Directory\Model\Country::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadByCode', 'getName'])
            ->getMock();
        $this->countryFactory->expects($this->atLeastOnce())->method('create')->willReturn($country);
        $country->expects($this->atLeastOnce())->method('loadByCode')->with($countryId)->willReturnSelf();
        $country->expects($this->atLeastOnce())->method('getName')->willReturn($countryName);
        $this->assertEquals($countryName, $this->gridBlock->getCountryByCode($countryId));
    }
}
