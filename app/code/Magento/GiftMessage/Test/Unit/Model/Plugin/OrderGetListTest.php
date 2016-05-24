<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Unit\Model\Plugin;

class OrderGetListTest extends \PHPUnit_Framework_TestCase
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
    private $orderRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionMock;

    public function setUp()
    {
        $this->giftMessageOrderRepositoryMock = $this->getMock(
            \Magento\GiftMessage\Api\OrderRepositoryInterface::class
        );
        $this->giftMessageOrderItemRepositoryMock = $this->getMock(
            \Magento\GiftMessage\Api\OrderItemRepositoryInterface::class
        );
        $this->orderExtensionFactoryMock = $this->getMock(
            \Magento\Sales\Api\Data\OrderExtensionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->orderItemExtensionFactoryMock = $this->getMock(
            \Magento\Sales\Api\Data\OrderItemExtensionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->orderMock = $this->getMock(
            \Magento\Sales\Api\Data\OrderInterface::class
        );
        $this->orderExtensionMock = $this->getMock(
            \Magento\Sales\Api\Data\OrderExtension::class,
            ['getGiftMessage', 'setGiftMessage'],
            [],
            '',
            false
        );
        $this->giftMessageMock = $this->getMock(
            \Magento\GiftMessage\Api\Data\MessageInterface::class
        );

        $this->orderRepositoryMock = $this->getMock(
            \Magento\Sales\Api\OrderRepositoryInterface::class
        );

        $this->collectionMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Collection::class,
            [],
            [],
            '',
            false
        );
        $this->plugin = new \Magento\GiftMessage\Model\Plugin\OrderGetList(
            $this->giftMessageOrderRepositoryMock,
            $this->giftMessageOrderItemRepositoryMock,
            $this->orderExtensionFactoryMock,
            $this->orderItemExtensionFactoryMock
        );
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
