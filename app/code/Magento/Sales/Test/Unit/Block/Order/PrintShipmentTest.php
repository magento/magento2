<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Block\Order;

use Magento\Sales\Model\ResourceModel\Order\Item\Collection as ItemCollection;

class PrintShipmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $contextMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $registryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $itemCollectionMock;

    /**
     * @var \Magento\Sales\Block\Order\PrintShipment
     */
    private $block;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentHelperMock = $this->getMockBuilder(\Magento\Payment\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $addressRendererMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Address\Renderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = new \Magento\Sales\Block\Order\PrintShipment(
            $this->contextMock,
            $this->registryMock,
            $paymentHelperMock,
            $addressRendererMock
        );

        $this->itemCollectionMock = $this->getMockBuilder(ItemCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testIsPagerDisplayed()
    {
        $this->assertFalse($this->block->isPagerDisplayed());
    }

    public function testGetItemsNoOrder()
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('current_order')
            ->willReturn(null);
        $this->assertEmpty($this->block->getItems());
    }

    public function testGetItemsSuccessful()
    {
        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $items = [5, 3, 1];

        $this->registryMock->expects($this->exactly(2))
            ->method('registry')
            ->with('current_order')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getItemsCollection')
            ->willReturn($this->itemCollectionMock);
        $this->itemCollectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn($items);

        $this->assertEquals($items, $this->block->getItems());
    }
}
