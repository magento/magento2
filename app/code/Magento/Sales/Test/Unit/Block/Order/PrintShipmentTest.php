<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Order;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Helper\Data;
use Magento\Sales\Block\Order\PrintShipment;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as ItemCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PrintShipmentTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $contextMock;

    /**
     * @var MockObject
     */
    private $registryMock;

    /**
     * @var MockObject
     */
    private $itemCollectionMock;

    /**
     * @var PrintShipment
     */
    private $block;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $addressRendererMock = $this->getMockBuilder(Renderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = new PrintShipment(
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
        $orderMock = $this->getMockBuilder(Order::class)
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
