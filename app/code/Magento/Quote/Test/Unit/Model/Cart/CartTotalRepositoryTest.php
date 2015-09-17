<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Cart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CartTotalRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterMock;

    /**
     * @var \Magento\Quote\Model\Cart\CartTotalRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $totalsFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $couponServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $totalsConverterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerMock;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->totalsFactoryMock = $this->getMock(
            'Magento\Quote\Api\Data\TotalsInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote',
            [
                'isVirtual',
                'getShippingAddress',
                'getAllVisibleItems',
                'getBaseCurrencyCode',
                'getQuoteCurrencyCode',
                'getItemsQty'
            ],
            [],
            '',
            false
        );
        $this->quoteRepositoryMock = $this->getMock('Magento\Quote\Model\QuoteRepository', [], [], '', false);
        $this->addressMock = $this->getMock('Magento\Quote\Model\Quote\Address', ['getData'], [], '', false);
        $this->dataObjectHelperMock = $this->getMockBuilder('\Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->converterMock = $this->getMock(
            'Magento\Quote\Model\Cart\Totals\ItemConverter',
            [],
            [],
            '',
            false
        );

        $this->couponServiceMock = $this->getMock('\Magento\Quote\Api\CouponManagementInterface');
        $this->totalsConverterMock = $this->getMock('\Magento\Quote\Model\Cart\TotalsConverter', [], [], '', false);
        $this->readerMock = $this->getMock('\Magento\Quote\Model\Quote\TotalsReader', [], [], '', false);

        $this->model = new \Magento\Quote\Model\Cart\CartTotalRepository(
            $this->totalsFactoryMock,
            $this->quoteRepositoryMock,
            $this->dataObjectHelperMock,
            $this->couponServiceMock,
            $this->totalsConverterMock,
            $this->converterMock,
            $this->readerMock
        );
    }

    public function testGet()
    {
        $cartId = 12;
        $itemsQty = 100;
        $coupon = 'coupon';
        $addressTotals = ['address' => 'totals'];
        $calculatedTotals = ['calculated' => 'totals'];
        $itemMock = $this->getMock('Magento\Quote\Model\Quote\Item', [], [], '', false);
        $visibleItems = [
            11 => $itemMock,
        ];
        $itemArray = [
            'name' => 'item',
            'options' => [ 4 => ['label' => 'justLabel']],
        ];
        $currencyCode = 'US';

        $this->quoteRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects($this->once())->method('getShippingAddress')->willReturn($this->addressMock);
        $this->quoteMock->expects($this->once())->method('getAllVisibleItems')->willReturn($visibleItems);
        $this->quoteMock->expects($this->once())->method('getBaseCurrencyCode')->willReturn($currencyCode);
        $this->quoteMock->expects($this->once())->method('getQuoteCurrencyCode')->willReturn($currencyCode);
        $this->quoteMock->expects($this->once())->method('getItemsQty')->willReturn($itemsQty);
        $this->addressMock->expects($this->once())->method('getData')->willReturn($addressTotals);

        $totalsMock = $this->getMock('\Magento\Quote\Api\Data\TotalsInterface');
        $this->totalsFactoryMock->expects($this->once())->method('create')->willReturn($totalsMock);
        $this->dataObjectHelperMock->expects($this->once())->method('populateWithArray');
        $this->converterMock->expects($this->once())
            ->method('modelToDataObject')
            ->with($itemMock)
            ->willReturn($itemArray);
        $this->readerMock->expects($this->once())
            ->method('fetch')
            ->with($this->quoteMock, $addressTotals)
            ->willReturn($calculatedTotals);

        $totalSegmentsMock = $this->getMock('\Magento\Quote\Api\Data\TotalSegmentInterface');
        $this->totalsConverterMock->expects($this->once())
            ->method('process')
            ->with($calculatedTotals)
            ->willReturn($totalSegmentsMock);

        $this->couponServiceMock->expects($this->once())->method('get')->with($cartId)->willReturn($coupon);

        $totalsMock->expects($this->once())->method('setItems')->with([11 => $itemArray,])->willReturnSelf();
        $totalsMock->expects($this->once())->method('setTotalSegments')->with($totalSegmentsMock)->willReturnSelf();
        $totalsMock->expects($this->once())->method('setCouponCode')->with($coupon)->willReturnSelf();
        $totalsMock->expects($this->once())->method('setGrandTotal')->willReturnSelf();
        $totalsMock->expects($this->once())->method('setItemsQty')->with($itemsQty)->willReturnSelf();
        $totalsMock->expects($this->once())->method('setBaseCurrencyCode')->with($currencyCode)->willReturnSelf();
        $totalsMock->expects($this->once())->method('setQuoteCurrencyCode')->with($currencyCode)->willReturnSelf();

        $this->assertEquals($totalsMock, $this->model->get($cartId));
    }
}
