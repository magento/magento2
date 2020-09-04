<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Grid;

use Magento\Framework\Registry;
use Magento\Sales\Model\Grid\CollectionUpdater;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CollectionUpdaterTest extends TestCase
{
    /**
     * @var CollectionUpdater
     */
    protected $collectionUpdater;

    /**
     * @var MockObject
     */
    protected $registryMock;

    protected function setUp(): void
    {
        $this->registryMock = $this->createMock(Registry::class);

        $this->collectionUpdater = new CollectionUpdater(
            $this->registryMock
        );
    }

    public function testUpdateIfOrderNotExists()
    {
        $collectionMock = $this->createMock(
            Collection::class
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
            ->with(['increment_id'])->willReturnSelf();
        $this->assertEquals($collectionMock, $this->collectionUpdater->update($collectionMock));
    }

    public function testUpdateIfOrderExists()
    {
        $collectionMock = $this->createMock(
            Collection::class
        );
        $orderMock = $this->createMock(Order::class);
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
            ->with(['increment_id'])->willReturnSelf();
        $this->assertEquals($collectionMock, $this->collectionUpdater->update($collectionMock));
    }
}
