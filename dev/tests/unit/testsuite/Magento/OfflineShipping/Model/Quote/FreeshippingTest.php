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
                'getFreeShipping'
            ])
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->calculatorMock = $this->getMockBuilder('Magento\OfflineShipping\Model\SalesRule\Calculator')
            ->disableOriginalConstructor()
            //->setMethods(['init'])
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
     */
    public function testCollectWithAddressItems(
        $addressItemMockNoDiscountValue,
        $addressMockGetFreeShippingExpects
    ) {
        $quoteMock = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $addressItemMock = $this->getMockBuilder('Magento\Sales\Model\Quote\Address\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getNoDiscount', 'getFreeShipping'])
            ->getMock();

        $addressItemMock->expects($this->once())
            ->method('getNoDiscount')
            ->willReturn($addressItemMockNoDiscountValue);

        $addressItemMock->expects($this->any())
            ->method('getFreeShipping')
            ->willReturn(true);

        $this->addressMock->expects($addressMockGetFreeShippingExpects)
            ->method('getFreeShipping')
            ->willReturn(false);

        $storeMock = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->calculatorMock->expects($this->once())
            ->method('init');

        $this->addressMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$addressItemMock]);

        $this->model->collect($this->addressMock);
    }

    public function scenariosDataProvider()
    {
        return [
            [true, $this->never()],
            [false, $this->once()]
        ];
    }
}
