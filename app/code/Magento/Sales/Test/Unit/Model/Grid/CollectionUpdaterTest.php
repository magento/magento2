<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Grid;

class CollectionUpdaterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Grid\CollectionUpdater
     */
    protected $collectionUpdater;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $registryMock;

    protected function setUp(): void
    {
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);

        $this->collectionUpdater = new \Magento\Sales\Model\Grid\CollectionUpdater(
            $this->registryMock
        );
    }

    public function testUpdateIfOrderNotExists()
    {
        $collectionMock = $this->createMock(
            \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection::class
        );
        $this->registryMock
            ->expects($this->once())
            ->method('registry')
            ->with('current_order')
            ->willReturn(false);
        $collectionMock->expects($this->never())->method('setOrderFilter');
        $collectionMock
            ->expects($this->once())
            ->method('addOrderInformation')
            ->with(['increment_id'])
            ->willReturnSelf();
        $this->assertEquals($collectionMock, $this->collectionUpdater->update($collectionMock));
    }

    public function testUpdateIfOrderExists()
    {
        $collectionMock = $this->createMock(
            \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection::class
        );
        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->registryMock
            ->expects($this->once())
            ->method('registry')
            ->with('current_order')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())->method('getId')->willReturn('orderId');
        $collectionMock->expects($this->once())->method('setOrderFilter')->with('orderId')->willReturnSelf();
        $collectionMock
            ->expects($this->once())
            ->method('addOrderInformation')
            ->with(['increment_id'])
            ->willReturnSelf();
        $this->assertEquals($collectionMock, $this->collectionUpdater->update($collectionMock));
    }
}
