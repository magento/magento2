<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Admin;

use Magento\Sales\Model\Order\Admin\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $orderItemMock;

    /** @var Item */
    protected $item;

    protected function setUp(): void
    {
        $this->orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->item = new Item();
    }

    public function testGetSku()
    {
        $sku = 'sku';
        $this->orderItemMock->expects($this->once())
            ->method('getSku')
            ->willReturn($sku);
        $result = $this->item->getSku($this->orderItemMock);
        $this->assertEquals($sku, $result);
    }

    public function testGetName()
    {
        $name = 'name';
        $this->orderItemMock->expects($this->once())
            ->method('getName')
            ->willReturn($name);
        $result = $this->item->getName($this->orderItemMock);
        $this->assertEquals($name, $result);
    }

    public function testGetProductId()
    {
        $productId = 1;
        $this->orderItemMock->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);
        $result = $this->item->getProductId($this->orderItemMock);
        $this->assertEquals($productId, $result);
    }
}
