<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Model\Plugin;

use Magento\GiftMessage\Api\Data\MessageInterface;
use Magento\GiftMessage\Api\OrderItemRepositoryInterface;
use Magento\GiftMessage\Api\OrderRepositoryInterface;
use Magento\GiftMessage\Model\Plugin\OrderGet;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemExtension;
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
        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getGiftMessageId'])
            ->getMockForAbstractClass();
        $this->orderExtensionMock = $this->getMockBuilder(OrderExtension::class)
            ->disableOriginalConstructor()
            ->addMethods(['getGiftMessage', 'setGiftMessage'])
            ->getMock();
        $this->giftMessageMock = $this->createMock(
            MessageInterface::class
        );
        $this->orderItemMock = $this->getMockBuilder(OrderItemInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getGiftMessageId'])
            ->getMockForAbstractClass();
        $this->orderItemExtensionMock = $this->getMockBuilder(OrderItemExtension::class)
            ->disableOriginalConstructor()
            ->addMethods(['getGiftMessage', 'setGiftMessage'])
            ->getMock();
        $this->orderRepositoryMock = $this->createMock(
            \Magento\Sales\Api\OrderRepositoryInterface::class
        );

        $this->collectionMock = $this->createMock(Collection::class);

        $this->plugin = new OrderGet(
            $this->giftMessageOrderRepositoryMock,
            $this->giftMessageOrderItemRepositoryMock
        );
    }

    /**
     * @return void
     */
    public function testAfterGetGiftMessageOnOrderLevel(): void
    {
        //set Gift Message for Order
        $orderId = 1;
        $messageId = 1;
        $this->orderMock->expects($this->once())->method('getGiftMessageId')->willReturn($messageId);
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

    /**
     * @return void
     */
    public function testAfterGetGiftMessageOnItemLevel(): void
    {
        //set Gift Message for Order
        $orderId = 1;
        $orderItemId = 2;
        $messageId = 1;
        $this->orderItemMock->expects($this->once())->method('getItemId')->willReturn($orderItemId);
        $this->orderItemMock->expects($this->once())->method('getGiftMessageId')->willReturn($messageId);
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

    /**
     * @return void
     */
    public function testGetAfterWhenMessagesAreNotSet(): void
    {
        //set Gift Message for Order
        $this->orderMock->expects($this->never())->method('getEntityId');
        $this->orderItemMock->expects($this->never())->method('getItemId');
        $this->orderItemMock->expects($this->once())->method('getGiftMessageId')->willReturn(null);
        $this->orderMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->orderExtensionMock);
        $this->orderExtensionMock->expects($this->once())->method('getGiftMessage')->willReturn([]);
        $this->giftMessageOrderRepositoryMock
            ->expects($this->never())
            ->method('get');
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
            ->expects($this->never())
            ->method('get');
        $this->orderItemExtensionMock
            ->expects($this->never())
            ->method('setGiftMessage');

        $this->plugin->afterGet($this->orderRepositoryMock, $this->orderMock);
    }

    /**
     * @return void
     */
    public function testAfterGetList(): void
    {
        //set Gift Message List for Order
        $orderId = 1;
        $messageId = 1;
        $this->orderMock->expects($this->once())->method('getEntityId')->willReturn($orderId);
        $this->orderMock->expects($this->once())->method('getGiftMessageId')->willReturn($messageId);
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
