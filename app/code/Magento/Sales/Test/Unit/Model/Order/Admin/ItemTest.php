<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Admin;

/**
 * Class ValidatorTest
 */
class ItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemMock;

    /** @var \Magento\Sales\Model\Order\Admin\Item */
    protected $item;

    protected function setUp()
    {
        $this->orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->item = new \Magento\Sales\Model\Order\Admin\Item();
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
