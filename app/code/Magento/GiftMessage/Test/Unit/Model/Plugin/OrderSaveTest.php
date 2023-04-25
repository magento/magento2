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
use Magento\GiftMessage\Model\Plugin\OrderSave;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemExtension;
use Magento\Sales\Api\Data\OrderItemInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

class OrderSaveTest extends TestCase
{
    /**
     * @var OrderSave
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

    protected function setUp(): void
    {
        $this->giftMessageOrderRepositoryMock = $this->createMock(
            OrderRepositoryInterface::class
        );
        $this->giftMessageOrderItemRepositoryMock = $this->createMock(
            OrderItemRepositoryInterface::class
        );
        $this->orderMock = $this->createMock(
            OrderInterface::class
        );
        $this->orderExtensionMock = $this->getOrderExtensionMock();
        $this->giftMessageMock = $this->createMock(
            MessageInterface::class
        );
        $this->orderItemMock = $this->createMock(
            OrderItemInterface::class
        );
        $this->orderItemExtensionMock = $this->getOrderItemExtensionMock();
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

    public function testAfterSaveIfGiftMessagesNotExist()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
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

    public function testAfterSaveIfItemGiftMessagesNotExist()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
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

    /**
     * Build order extension mock.
     *
     * @return MockObject
     */
    private function getOrderExtensionMock(): MockObject
    {
        $mockBuilder = $this->getMockBuilder(OrderExtension::class);
        try {
            $mockBuilder->addMethods(['getGiftMessage', 'setGiftMessage']);
        } catch (RuntimeException $e) {
            // OrderExtension already generated.
        }

        return $mockBuilder->getMock();
    }

    /**
     * Build order item extension mock.
     *
     * @return MockObject
     */
    private function getOrderItemExtensionMock(): MockObject
    {
        $mockBuilder = $this->getMockBuilder(OrderItemExtension::class);
        try {
            $mockBuilder->addMethods(['getGiftMessage', 'setGiftMessage']);
        } catch (RuntimeException $e) {
            // OrderItemExtension already generated.
        }

        return $mockBuilder->getMock();
    }
}
