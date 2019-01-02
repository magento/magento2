<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Address;

use Magento\Customer\Model\ResourceModel\Address\CollectionFactory;

/**
 * Unit tests for \Magento\Customer\Block\Address\Book class
 */
class BookTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Directory\Model\CountryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $countryFactory;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $addressCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $currentCustomer;

    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageConfig;

    /**
     * @var \Magento\Customer\Block\Address\Book
     */
    private $bookBlock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->countryFactory = $this->getMockBuilder(\Magento\Directory\Model\CountryFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->currentCustomer = $this->getMockBuilder(\Magento\Customer\Helper\Session\CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomer'])
            ->getMock();

        $this->addressCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->pageConfig = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTitle'])
            ->getMock();

        $this->bookBlock = $this->objectManager->getObject(
            \Magento\Customer\Block\Address\Book::class,
            [
                'countryFactory' => $this->countryFactory,
                'addressCollectionFactory' => $this->addressCollectionFactory,
                'currentCustomer' => $this->currentCustomer,
                'pageConfig' => $this->pageConfig
            ]
        );
    }

    /**
     * Test for \Magento\Customer\Block\Address\Book::getStreetAddress method
     */
    public function testGetStreetAddress()
    {
        $street = ['Line 1', 'Line 2'];
        $expectedAddress = 'Line 1, Line 2';
        $this->assertEquals($expectedAddress, $this->bookBlock->getStreetAddress($street));
    }

    /**
     * Test for \Magento\Customer\Block\Address\Book::getPagerHtml method
     */
    public function testGetPagerHtml()
    {
        $customerId = 1;

        /** @var \Magento\Framework\View\Element\BlockInterface|\PHPUnit_Framework_MockObject_MockObject $block */
        $block = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)
            ->setMethods(['setCollection'])
            ->getMockForAbstractClass();
        /** @var  $layout \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject */
        $layout = $this->getMockForAbstractClass(\Magento\Framework\View\LayoutInterface::class);
        /** @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject $title */
        $title = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->setMethods(['set'])
            ->getMock();
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
        $title->expects($this->atLeastOnce())->method('set')->with(__('Address Book'))->willReturnSelf();
        $this->pageConfig->expects($this->atLeastOnce())->method('getTitle')->willReturn($title);
        $customer->expects($this->atLeastOnce())->method('getId')->willReturn($customerId);
        $this->currentCustomer->expects($this->atLeastOnce())->method('getCustomer')->willReturn($customer);
        $addressCollection->expects($this->atLeastOnce())->method('setOrder')->with('entity_id', 'desc')
            ->willReturnSelf();
        $addressCollection->expects($this->atLeastOnce())->method('setCustomerFilter')->with([$customerId])
            ->willReturnSelf();
        $this->addressCollectionFactory->expects($this->atLeastOnce())->method('create')
            ->willReturn($addressCollection);
        $block->expects($this->atLeastOnce())->method('setCollection')->with($addressCollection)->willReturnSelf();
        $this->bookBlock->setNameInLayout('NameInLayout');
        $this->bookBlock->setLayout($layout);
        $this->assertEquals('OutputString', $this->bookBlock->getPagerHtml());
    }

    /**
     * Test for \Magento\Customer\Block\Address\Book::getCountryById method
     */
    public function testGetCpuntryByid()
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
        $this->assertEquals($countryName, $this->bookBlock->getCountryById($countryId));
    }
}
