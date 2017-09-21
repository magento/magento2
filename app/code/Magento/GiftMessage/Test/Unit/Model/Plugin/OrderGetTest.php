<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Unit\Model\Plugin;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderGetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GiftMessage\Model\Plugin\OrderGet
     */
    private $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $giftMessageOrderRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $giftMessageOrderItemRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderExtensionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderItemExtensionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderExtensionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $giftMessageMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderItemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderItemExtensionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionMock;

    public function setUp()
    {
        $this->giftMessageOrderRepositoryMock = $this->createMock(
            \Magento\GiftMessage\Api\OrderRepositoryInterface::class
        );
        $this->giftMessageOrderItemRepositoryMock = $this->createMock(
            \Magento\GiftMessage\Api\OrderItemRepositoryInterface::class
        );
        $this->orderExtensionFactoryMock = $this->createPartialMock(
            \Magento\Sales\Api\Data\OrderExtensionFactory::class,
            ['create']
        );
        $this->orderItemExtensionFactoryMock = $this->createPartialMock(
            \Magento\Sales\Api\Data\OrderItemExtensionFactory::class,
            ['create']
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

        $this->collectionMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Collection::class);

        $this->plugin = new \Magento\GiftMessage\Model\Plugin\OrderGet(
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
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
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
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
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
