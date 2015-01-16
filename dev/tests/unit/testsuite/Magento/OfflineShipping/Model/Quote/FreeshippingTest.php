<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Model\Quote;

class FreeshippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflineShipping\Model\Quote\Freeshipping|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Sales\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\OfflineShipping\Model\SalesRule\Calculator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculatorMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->addressMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods([
                'getQuote',
                'getAllItems',
                'getFreeShipping',
                'setFreeShipping'
            ])
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->calculatorMock = $this->getMockBuilder('Magento\OfflineShipping\Model\SalesRule\Calculator')
            ->disableOriginalConstructor()
            ->setMethods(['init', 'processFreeShipping'])
            ->getMock();

        $this->model = $helper->getObject('Magento\OfflineShipping\Model\Quote\Freeshipping', [
            'storeManager' => $this->storeManagerMock,
            'calculator' => $this->calculatorMock
        ]);
    }


    public function testCollectWithEmptyAddressItems()
    {
        $quoteMock = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->addressMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([]);

        $this->assertSame($this->model->collect($this->addressMock), $this->model);
    }

    /**
     * @dataProvider scenariosDataProvider
     * @param $isNoDiscount
     */
    public function testCollectWithAddressItems($isNoDiscount)
    {
        $quoteMock = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $addressItemMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Address\Item')
            ->disableOriginalConstructor()
            ->setMethods([
                'getNoDiscount',
                'setFreeShipping',
                'getParentItemId',
                'getFreeShipping',
                'getHasChildren',
                'isChildrenCalculated',
                'getChildren'
            ])
            ->getMock();

        $this->addressMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$addressItemMock]);

        $this->calculatorMock->expects($this->once())
            ->method('init');

        $addressItemMock->expects($this->once())
            ->method('getNoDiscount')
            ->willReturn($isNoDiscount);

        $addressItemMock->expects($isNoDiscount ? $this->once() : $this->never())
            ->method('setFreeShipping');

        $addressItemMock->expects($isNoDiscount ? $this->never() : $this->once())
            ->method('getParentItemId')
            ->willReturn(false);

        $this->calculatorMock->expects($isNoDiscount ? $this->never() : $this->exactly(2))
            ->method('processFreeShipping');

        $addressItemMock->expects($isNoDiscount ? $this->never() : $this->once())
            ->method('getFreeShipping')
            ->willReturn(true);

        $addressItemMock->expects($isNoDiscount ? $this->never() : $this->once())
            ->method('getHasChildren')
            ->willReturn(true);

        $addressItemMock->expects($isNoDiscount ? $this->never() : $this->once())
            ->method('isChildrenCalculated')
            ->willReturn(true);

        $childMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Item\AbstractItem')
            ->disableOriginalConstructor()
            ->setMethods(['setFreeShipping', 'getQuote', 'getAddress', 'getOptionByCode'])
            ->getMock();

        $addressItemMock->expects($isNoDiscount ? $this->never() : $this->once())
            ->method('getChildren')
            ->willReturn([$childMock]);

        $childMock->expects($isNoDiscount ? $this->never() : $this->once())
            ->method('setFreeShipping');

        $this->addressMock->expects($isNoDiscount ? $this->never() : $this->once())
            ->method('getFreeShipping')
            ->willReturn(false);

        $this->addressMock->expects($isNoDiscount ? $this->once() : $this->exactly(2))
            ->method('setFreeShipping');

        $this->assertSame($this->model->collect($this->addressMock), $this->model);
    }

    public function scenariosDataProvider()
    {
        return [
            [
                true,  // there is no a discount
            ],
            [
                false, // there is a discount
            ]
        ];
    }
}
