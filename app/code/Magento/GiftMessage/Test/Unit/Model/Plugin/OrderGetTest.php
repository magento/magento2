<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Model\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\GiftMessage\Api\OrderItemRepositoryInterface;
use Magento\GiftMessage\Api\OrderRepositoryInterface;
use Magento\GiftMessage\Model\Plugin\OrderGet;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemExtension;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderGetTest extends TestCase
{
    /**
     * @var OrderGet
     */
    private $plugin;

    /**
     * @var MockObject
     */
    private $giftMessageOrderRepositoryMock;

    /**
     * @var MockObject
     */
    private $giftMessageOrderItemRepositoryMock;

    /**
     * @var MockObject
     */
    private $orderExtensionFactoryMock;

    /**
     * @var MockObject
     */
    private $orderItemExtensionFactoryMock;

    /**
     * @var MockObject
     */
    private $orderMock;

    /**
     * @var MockObject
     */
    private $orderExtensionMock;

    /**
     * @var MockObject
     */
    private $giftMessageMock;

    /**
     * @var MockObject
     */
    private $orderItemMock;

    /**
     * @var MockObject
     */
    private $orderItemExtensionMock;

    /**
     * @var MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var MockObject
     */
    private $collectionMock;

    protected function setUp(): void
    {
        $this->giftMessageOrderRepositoryMock = $this->createMock(
            OrderRepositoryInterface::class
        );
        $this->giftMessageOrderItemRepositoryMock = $this->createMock(
            OrderItemRepositoryInterface::class
        );
        $this->orderExtensionFactoryMock = $this->createPartialMock(
            OrderExtensionFactory::class,
            ['create']
        );
        $this->orderItemExtensionFactoryMock = $this->createPartialMock(
            OrderItemExtensionFactory::class,
            ['create']
        );
        $this->orderMock = $this->createMock(
            OrderInterface::class
        );
        $this->orderExtensionMock = $this->getMockBuilder(OrderExtension::class)
            ->addMethods(['getGiftMessage', 'setGiftMessage'])
            ->getMock();
        $this->giftMessageMock = $this->createMock(
            MessageInterface::class
        );
        $this->orderItemMock = $this->createMock(
            OrderItemInterface::class
        );
        $this->orderItemExtensionMock = $this->getMockBuilder(OrderItemExtension::class)
            ->addMethods(['setGiftMessage', 'getGiftMessage'])
            ->getMock();
        $this->orderRepositoryMock = $this->createMock(
            \Magento\Sales\Api\OrderRepositoryInterface::class
        );

        $this->collectionMock = $this->createMock(Collection::class);

        $this->plugin = new OrderGet(
            $this->giftMessageOrderRepositoryMock,
            $this->giftMessageOrderItemRepositoryMock,
            $this->orderExtensionFactoryMock,
            $this->orderItemExtensionFactoryMock
        );
    }

    public function testAfterGetGiftMessageOnOrderLevel()
    {
        //set Gift Message for Order
        $orderId = 1;
        $this->orderMock->expects($this->once())->method('getEntityId')->willReturn($orderId);
        $this->orderMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->orderExtensionMock);
        $this->orderExtensionMock->expects($this->once())->method('getGiftMessage')->willReturn([]);
        $this->giftMessageOrderRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($this->giftMessageMock);
        $this->orderExtensionMock
            ->expects($this->once())
            ->method('setGiftMessage')
            ->with($this->giftMessageMock)
            ->willReturnSelf();
        $this->orderMock
            ->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->orderExtensionMock)
            ->willReturnSelf();

        // set Gift Message on Item Level
        $this->orderMock->expects($this->once())->method('getItems')->willReturn([]);
        $this->plugin->afterGet($this->orderRepositoryMock, $this->orderMock);
    }

    public function testAfterGetGiftMessageOnItemLevel()
    {
        //set Gift Message for Order
        $orderId = 1;
        $orderItemId = 2;
        $this->orderItemMock->expects($this->once())->method('getItemId')->willReturn($orderItemId);
        $this->orderMock->expects($this->once())->method('getEntityId')->willReturn($orderId);
        $this->orderMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->orderExtensionMock);
        $this->orderExtensionMock->expects($this->once())->method('getGiftMessage')->willReturn($this->giftMessageMock);

        // set Gift Message on Item Level
        $this->orderMock->expects($this->once())->method('getItems')->willReturn([$this->orderItemMock]);
        $this->orderItemMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->orderItemExtensionMock);
        $this->orderItemExtensionMock->expects($this->once())->method('getGiftMessage')->willReturn([]);
        $this->giftMessageOrderItemRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($orderId, $orderItemId)
            ->willReturn($this->giftMessageMock);
        $this->orderItemExtensionMock
            ->expects($this->once())
            ->method('setGiftMessage')
            ->with($this->giftMessageMock)
            ->willReturnSelf();
        $this->orderItemMock
            ->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->orderItemExtensionMock)
            ->willReturnSelf();
        $this->plugin->afterGet($this->orderRepositoryMock, $this->orderMock);
    }

    public function testGetAfterWhenMessagesAreNotSet()
    {
        $orderId = 1;
        $orderItemId = 2;
        //set Gift Message for Order
        $this->orderMock->expects($this->exactly(2))->method('getEntityId')->willReturn($orderId);
        $this->orderItemMock->expects($this->once())->method('getItemId')->willReturn($orderItemId);
        $this->orderMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->orderExtensionMock);
        $this->orderExtensionMock->expects($this->once())->method('getGiftMessage')->willReturn([]);
        $this->giftMessageOrderRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willThrowException(new NoSuchEntityException());
        $this->orderExtensionMock
            ->expects($this->never())
            ->method('setGiftMessage');

        // set Gift Message on Item Level
        $this->orderMock->expects($this->once())->method('getItems')->willReturn([$this->orderItemMock]);
        $this->orderItemMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->orderItemExtensionMock);
        $this->orderItemExtensionMock->expects($this->once())->method('getGiftMessage')->willReturn([]);
        $this->giftMessageOrderItemRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($orderId, $orderItemId)
            ->willThrowException(new NoSuchEntityException());
        $this->orderItemExtensionMock
            ->expects($this->never())
            ->method('setGiftMessage');

        $this->plugin->afterGet($this->orderRepositoryMock, $this->orderMock);
    }

    public function testAfterGetList()
    {
        //set Gift Message List for Order
        $orderId = 1;
        $this->orderMock->expects($this->once())->method('getEntityId')->willReturn($orderId);
        $this->orderMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->orderExtensionMock);
        $this->orderExtensionMock->expects($this->once())->method('getGiftMessage')->willReturn([]);
        $this->giftMessageOrderRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with($orderId)
            ->willReturn($this->giftMessageMock);
        $this->orderExtensionMock
            ->expects($this->once())
            ->method('setGiftMessage')
            ->with($this->giftMessageMock)
            ->willReturnSelf();
        $this->orderMock
            ->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->orderExtensionMock)
            ->willReturnSelf();

        // set Gift Message on Item Level
        $this->orderMock->expects($this->once())->method('getItems')->willReturn([]);
        $this->collectionMock->expects($this->once())->method('getItems')->willReturn([$this->orderMock]);
        $this->plugin->afterGetList($this->orderRepositoryMock, $this->collectionMock);
    }
}
