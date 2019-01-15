<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Cart;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTotalRepositoryTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->totalsFactoryMock = $this->createPartialMock(
            \Magento\Quote\Api\Data\TotalsInterfaceFactory::class,
            ['create']
        );
        $this->quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, [
                'isVirtual',
                'getShippingAddress',
                'getBillingAddress',
                'getAllVisibleItems',
                'getBaseCurrencyCode',
                'getQuoteCurrencyCode',
                'getItemsQty'
            ]);
        $this->quoteRepositoryMock = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->addressMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Address::class,
            ['getData', 'getTotals']
        );
        $this->dataObjectHelperMock = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->converterMock = $this->createMock(\Magento\Quote\Model\Cart\Totals\ItemConverter::class);

        $this->couponServiceMock = $this->createMock(\Magento\Quote\Api\CouponManagementInterface::class);
        $this->totalsConverterMock = $this->createMock(\Magento\Quote\Model\Cart\TotalsConverter::class);

        $this->model = new \Magento\Quote\Model\Cart\CartTotalRepository(
            $this->totalsFactoryMock,
            $this->quoteRepositoryMock,
            $this->dataObjectHelperMock,
            $this->couponServiceMock,
            $this->totalsConverterMock,
            $this->converterMock
        );
    }

    /**
     * @param bool $isVirtual
     * @param string $getAddressType
     * @dataProvider getDataProvider
     */
    public function testGet($isVirtual, $getAddressType)
    {
        $cartId = 12;
        $itemsQty = 100;
        $coupon = 'coupon';
        $addressTotals = ['address' => 'totals'];
        $itemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
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
        $this->quoteMock->expects($this->once())->method('isVirtual')->willReturn($isVirtual);
        $this->quoteMock->expects($this->exactly(2))->method($getAddressType)->willReturn($this->addressMock);
        $this->quoteMock->expects($this->once())->method('getAllVisibleItems')->willReturn($visibleItems);
        $this->quoteMock->expects($this->once())->method('getBaseCurrencyCode')->willReturn($currencyCode);
        $this->quoteMock->expects($this->once())->method('getQuoteCurrencyCode')->willReturn($currencyCode);
        $this->quoteMock->expects($this->once())->method('getItemsQty')->willReturn($itemsQty);
        $this->addressMock->expects($this->any())->method('getData')->willReturn($addressTotals);
        $this->addressMock->expects($this->once())->method('getTotals')->willReturn($addressTotals);

        $totalsMock = $this->createMock(\Magento\Quote\Api\Data\TotalsInterface::class);
        $this->totalsFactoryMock->expects($this->once())->method('create')->willReturn($totalsMock);
        $this->dataObjectHelperMock->expects($this->once())->method('populateWithArray');
        $this->converterMock->expects($this->once())
            ->method('modelToDataObject')
            ->with($itemMock)
            ->willReturn($itemArray);

        $totalSegmentsMock = $this->createMock(\Magento\Quote\Api\Data\TotalSegmentInterface::class);
        $this->totalsConverterMock->expects($this->once())
            ->method('process')
            ->with($addressTotals)
            ->willReturn($totalSegmentsMock);

        $this->couponServiceMock->expects($this->once())->method('get')->with($cartId)->willReturn($coupon);

        $totalsMock->expects($this->once())->method('setItems')->with([11 => $itemArray])->willReturnSelf();
        $totalsMock->expects($this->once())->method('setTotalSegments')->with($totalSegmentsMock)->willReturnSelf();
        $totalsMock->expects($this->once())->method('setCouponCode')->with($coupon)->willReturnSelf();
        $totalsMock->expects($this->once())->method('setGrandTotal')->willReturnSelf();
        $totalsMock->expects($this->once())->method('setItemsQty')->with($itemsQty)->willReturnSelf();
        $totalsMock->expects($this->once())->method('setBaseCurrencyCode')->with($currencyCode)->willReturnSelf();
        $totalsMock->expects($this->once())->method('setQuoteCurrencyCode')->with($currencyCode)->willReturnSelf();

        $this->assertEquals($totalsMock, $this->model->get($cartId));
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        return [
            'Virtual Quote' => [
                'isVirtual' => true,
                'getAddressType' => 'getBillingAddress'
            ],
            'Non-virtual Quote' => [
                'isVirtual' => false,
                'getAddressType' => 'getShippingAddress'
            ]
        ];
    }
}
