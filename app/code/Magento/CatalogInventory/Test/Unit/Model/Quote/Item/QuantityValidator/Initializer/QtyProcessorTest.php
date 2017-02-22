<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Test\Unit\Model\Quote\Item\QuantityValidator\Initializer;

use \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\QtyProcessor;

class QtyProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QtyProcessor
     */
    protected $qtyProcessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteItemQtyList;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    public function setUp()
    {
        $this->quoteItemQtyList = $this->getMockBuilder(
            'Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\QuoteItemQtyList'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->qtyProcessor = new QtyProcessor($this->quoteItemQtyList);
        $this->itemMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->setMethods(['getParentItem', 'getProduct', '__wakeup'])
            ->getMock();
    }

    public function testSetItem()
    {
        $itemMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(
            'Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\QtyProcessor',
            $this->qtyProcessor->setItem($itemMock)
        );
    }

    public function testGetRowQty()
    {
        $qty = 1;

        $itemMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $parentItemMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->any())
            ->method('getParentItem')
            ->willReturn($parentItemMock);
        $parentItemMock->expects($this->once())
            ->method('getQty')
            ->willReturn($qty);

        $this->qtyProcessor->setItem($itemMock);
        $this->assertEquals($qty, $this->qtyProcessor->getRowQty($qty));
    }

    /**
     */
    public function testGetQtyForCheckNoParentItem()
    {
        $qty = 1;
        $productId = 1;

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $itemMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productMock);
        $this->quoteItemQtyList->expects($this->once())
            ->method('getQty')
            ->withAnyParameters()
            ->willReturn($qty);

        $this->qtyProcessor->setItem($itemMock);
        $this->assertEquals($qty, $this->qtyProcessor->getQtyForCheck($qty));
    }

    public function testGetQtyForCheck()
    {
        $qty = 1;
        $productId = 1;

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getId', '__wakeup'])
            ->getMock();
        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn($productId);
        $parentItemMock = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemMock->expects($this->any())
            ->method('getParentItem')
            ->willReturn($parentItemMock);
        $this->itemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productMock);
        $this->quoteItemQtyList->expects($this->once())
            ->method('getQty')
            ->withAnyParameters()
            ->willReturn($qty);

        $this->qtyProcessor->setItem($this->itemMock);
        $this->assertEquals($qty, $this->qtyProcessor->getQtyForCheck($qty));
    }
}
