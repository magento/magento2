<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote\Item;

/**
 * Class ToOrderItemTest
 */
class ToOrderItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\ToOrderItem
     */
    protected $converter;

    /**
     * @var \Magento\Sales\Api\Data\OrderItemInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderItemFactoryMock;

    /**
     * @var \Magento\Framework\DataObject\Copy|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectCopyServiceMock;

    /**
     * @var \Magento\Quote\Model\Quote\Item|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteItemMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Catalog\Model\Product\Type\Simple|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productTypeMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderItemInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderItemMock;

    protected function setUp(): void
    {
        $this->orderItemFactoryMock = $this->createPartialMock(
            \Magento\Sales\Api\Data\OrderItemInterfaceFactory::class,
            ['create']
        );
        $this->objectCopyServiceMock = $this->createMock(\Magento\Framework\DataObject\Copy::class);
        $this->quoteItemMock = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->productTypeMock = $this->createMock(\Magento\Catalog\Model\Product\Type\Simple::class);
        $this->orderItemMock = $this->createMock(\Magento\Sales\Model\Order\Item::class);
        $dataObjectHelper = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);

        $this->converter = new \Magento\Quote\Model\Quote\Item\ToOrderItem(
            $this->orderItemFactoryMock,
            $this->objectCopyServiceMock,
            $dataObjectHelper
        );
    }

    /**
     * test for convert method
     */
    public function testConvert()
    {
        $this->quoteItemMock->expects($this->exactly(2))
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($this->productTypeMock);
        $this->productTypeMock->expects($this->once())
            ->method('getOrderOptions')
            ->with($this->productMock)
            ->willReturn(['option']);
        $this->objectCopyServiceMock->expects($this->at(0))
            ->method('getDataFromFieldset')
            ->with('quote_convert_item', 'to_order_item', $this->quoteItemMock)
            ->willReturn([]);
        $this->objectCopyServiceMock->expects($this->at(1))
            ->method('getDataFromFieldset')
            ->with('quote_convert_item', 'to_order_item_discount', $this->quoteItemMock)
            ->willReturn([]);
        $this->orderItemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->orderItemMock);
        $this->assertInstanceOf(
            \Magento\Sales\Model\Order\Item::class,
            $this->converter->convert($this->quoteItemMock, [])
        );
    }
}
