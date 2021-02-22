<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Unit\Model\Plugin;

use Magento\GiftMessage\Model\Plugin\OrderSave;

class OrderSaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderSave
     */
    private $plugin;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $giftMessageOrderRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $giftMessageOrderItemRepositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $orderExtensionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $giftMessageMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $orderItemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $orderItemExtensionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRepositoryMock;

    protected function setUp(): void
    {
        $this->giftMessageOrderRepositoryMock = $this->createMock(
            \Magento\GiftMessage\Api\OrderRepositoryInterface::class
        );
        $this->giftMessageOrderItemRepositoryMock = $this->createMock(
            \Magento\GiftMessage\Api\OrderItemRepositoryInterface::class
        );
        $this->orderMock = $this->createMock(
            \Magento\Sales\Api\Data\OrderInterface::class
        );
        $this->orderExtensionMock = $this->createPartialMock(
            \Magento\Sales\Api\Data\OrderExtension::class,
            ['getGiftMessage', 'setGiftMessage']
        );
        $this->giftMessageMock = $this->createMock(
            \Magento\GiftMessage\Api\Data\MessageInterface::class
        );
        $this->orderItemMock = $this->createMock(
            \Magento\Sales\Api\Data\OrderItemInterface::class
        );
        $this->orderItemExtensionMock = $this->createPartialMock(
            \Magento\Sales\Api\Data\OrderItemExtension::class,
            ['setGiftMessage', 'getGiftMessage']
        );
        $this->orderRepositoryMock = $this->createMock(
            \Magento\Sales\Api\OrderRepositoryInterface::class
        );

        $this->plugin = new OrderSave(
            $this->giftMessageOrderRepositoryMock,
            $this->giftMessageOrderItemRepositoryMock
        );
    }

    public function testAfterSaveGiftMessages()
    {
        // save Gift Message on order level
        $orderId = 1;
        $orderItemId = 2;
        $this->orderMock->expects($this->exactly(2))->method('getEntityId')->willReturn($orderId);
        $this->orderItemMock->expects($this->once())->method('getItemId')->willReturn($orderItemId);
        $this->orderMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->orderExtensionMock);
        $this->orderExtensionMock
            ->expects($this->exactly(2))
            ->method('getGiftMessage')
            ->willReturn($this->giftMessageMock);
        $this->giftMessageOrderRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($orderId, $this->giftMessageMock);

        // save Gift Messages on item level
        $this->orderMock->expects($this->once())->method('getItems')->willReturn([$this->orderItemMock]);
        $this->orderItemMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->orderItemExtensionMock);
        $this->orderItemExtensionMock
            ->expects($this->exactly(2))
            ->method('getGiftMessage')
            ->willReturn($this->giftMessageMock);
        $this->giftMessageOrderItemRepositoryMock
            ->expects($this->once())->method('save')
            ->with($orderId, $orderItemId, $this->giftMessageMock);
        $this->plugin->afterSave($this->orderRepositoryMock, $this->orderMock);
    }

    /**
     */
    public function testAfterSaveIfGiftMessagesNotExist()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->expectExceptionMessage('The gift message couldn\'t be added to the "Test message" order.');

        // save Gift Message on order level
        $orderId = 1;
        $this->orderMock->expects($this->once())->method('getEntityId')->willReturn($orderId);
        $this->orderMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->orderExtensionMock);
        $this->orderExtensionMock
            ->expects($this->exactly(2))
            ->method('getGiftMessage')
            ->willReturn($this->giftMessageMock);
        $this->giftMessageOrderRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new \Exception('Test message'));

        // save Gift Messages on item level
        $this->orderMock->expects($this->never())->method('getItems');
        $this->plugin->afterSave($this->orderRepositoryMock, $this->orderMock);
    }

    /**
     */
    public function testAfterSaveIfItemGiftMessagesNotExist()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        $this->expectExceptionMessage('The gift message couldn\'t be added to the "Test message" order item.');

        // save Gift Message on order level
        $orderId = 1;
        $orderItemId = 2;
        $this->orderMock->expects($this->once())->method('getEntityId')->willReturn($orderId);
        $this->orderItemMock->expects($this->once())->method('getItemId')->willReturn($orderItemId);
        $this->orderMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn(null);
        $this->orderExtensionMock
            ->expects($this->never())
            ->method('getGiftMessage');

        // save Gift Messages on item level
        $this->orderMock->expects($this->once())->method('getItems')->willReturn([$this->orderItemMock]);
        $this->orderItemMock
            ->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->orderItemExtensionMock);
        $this->orderItemExtensionMock
            ->expects($this->exactly(2))
            ->method('getGiftMessage')
            ->willReturn($this->giftMessageMock);
        $this->giftMessageOrderItemRepositoryMock
            ->expects($this->once())->method('save')
            ->with($orderId, $orderItemId, $this->giftMessageMock)
            ->willThrowException(new \Exception('Test message'));
        $this->plugin->afterSave($this->orderRepositoryMock, $this->orderMock);
    }
}
